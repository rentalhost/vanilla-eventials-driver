<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Rentalhost\Vanilla\EventialsDriver\Services\ArrayService;
use Rentalhost\Vanilla\EventialsDriver\Services\TypeService;
use Rentalhost\Vanilla\EventialsDriver\Types\SubscriptionUsersTypeArray;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarFormTypeArray;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarParticipantsTypeArray;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarParticipantType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarRequestType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarsTypeArray;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarSubscriberType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarType;

class DriverHandler
{
    private GuzzleClient $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function addWebinarParticipant(int $webinarId, string $participantEmail, ?string $accessCode = null): WebinarParticipantType
    {
        /** @var WebinarParticipantsTypeArray $type */
        $type = TypeService::arrayFromGuzzleResponse($this->guzzleClient->post('webinars/' . $webinarId . '/access-control', [
            'json' => [
                'send_invitation' => false,
                'invites'         => [
                    array_filter([
                        'email'       => $participantEmail,
                        'access_code' => $accessCode
                    ])
                ]
            ]
        ]), WebinarParticipantsTypeArray::class);

        return $type[0];
    }

    public function getSubscribedUsers(int $webinarId): SubscriptionUsersTypeArray
    {
        /** @var SubscriptionUsersTypeArray $type */
        $type = TypeService::arrayFromGuzzleResponse($this->guzzleClient->get('webinars/' . $webinarId . '/registrations'), SubscriptionUsersTypeArray::class);

        return $type;
    }

    public function getWebinar(int $webinarId): WebinarType
    {
        /** @var WebinarType $type */
        $type = TypeService::fromGuzzleResponse($this->guzzleClient->get('webinars/' . $webinarId), WebinarType::class);

        return $type;
    }

    public function getWebinarForm(int $webinarId): WebinarFormTypeArray
    {
        /** @var WebinarFormTypeArray $type */
        $type = TypeService::arrayFromGuzzleResponse($this->guzzleClient->get('webinars/' . $webinarId . '/form'), WebinarFormTypeArray::class);

        return $type;
    }

    public function getWebinarParticipants(int $webinarId): WebinarParticipantsTypeArray
    {
        /** @var WebinarParticipantsTypeArray $type */
        $type = TypeService::arrayFromGuzzleResponse($this->guzzleClient->get('webinars/' . $webinarId . '/access-control'), WebinarParticipantsTypeArray::class);

        return $type;
    }

    public function getWebinarWithUrl(string $webinarUrl): ?WebinarType
    {
        $webinarUrl = rtrim($webinarUrl, '/');

        /** @var WebinarType $webinar */
        foreach ($this->getWebinars() as $webinar) {
            if ($webinar->url === $webinarUrl) {
                return $webinar;
            }
        }

        return null;
    }

    public function getWebinars(?bool $isPublic = null, ?bool $isGroupOnly = null, ?bool $isDraft = null, ?string $state = null, ?string $slug = null): WebinarsTypeArray
    {
        /** @var WebinarsTypeArray $typeArray */
        $typeArray = TypeService::arrayFromGuzzleResponse($this->guzzleClient->get('webinars', [
            'query' => ArrayService::exceptNull([
                'is_public'     => $isPublic,
                'is_group_only' => $isGroupOnly,
                'is_draft'      => $isDraft,
                'state'         => $state,
                'slug'          => $slug
            ])
        ]), WebinarsTypeArray::class);

        return $typeArray;
    }

    public function removeWebinar(int $webinarId): void
    {
        $this->guzzleClient->delete('webinars/' . $webinarId);
    }

    public function removeWebinarParticipant(int $webinarId, int $participantId): void
    {
        try {
            $this->guzzleClient->delete('webinars/' . $webinarId . '/access-control/' . $participantId);
        }
        catch (ClientException $exception) {
            if ($exception->getCode() === 404) {
                return;
            }

            throw $exception;
        }
    }

    public function removeWebinarParticipantWithEmail(int $webinarId, string $email): void
    {
        $participant = $this->getWebinarParticipants($webinarId)->getByEmail($email);

        if ($participant) {
            $this->removeWebinarParticipant($webinarId, $participant->id);
        }
    }

    public function scheduleWebinar(WebinarRequestType $webinarRequest): WebinarType
    {
        /** @var WebinarType $type */
        $type = TypeService::fromGuzzleResponse($this->guzzleClient->post('webinars', [
            'json' => $webinarRequest->toArray()
        ]), WebinarType::class);

        return $type;
    }

    public function subscribeUser(int $webinarId, WebinarSubscriberType $subscriber): void
    {
        try {
            $this->guzzleClient->post('webinars/' . $webinarId . '/subscribe', [
                'json' => [ $subscriber->toArray() ]
            ]);
        }
        catch (ClientException $exception) {
            if ($exception->getCode() === 400) {
                $exceptionData = json_decode($exception->getResponse()->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                if (str_contains($exceptionData['errors'][0]['error'], 'already registered')) {
                    return;
                }
            }

            throw $exception;
        }
    }

    public function updateWebinar(int $webinarId, WebinarRequestType $webinarRequest): void
    {
        $this->guzzleClient->put('webinars/' . $webinarId, [
            'json' => $webinarRequest->toArray()
        ]);
    }
}
