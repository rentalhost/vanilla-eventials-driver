<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\EventialsDriver\DriverConnector;
use Rentalhost\Vanilla\EventialsDriver\DriverHandler;
use Rentalhost\Vanilla\EventialsDriver\Tests\Fixtures\Types\CustomSubscriptionFormType;
use Rentalhost\Vanilla\EventialsDriver\Types\CredentialsType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarCategoryType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarOwnerType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarParticipantType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarRequestType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarStreamType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarSubscriberType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarTimezoneType;
use Rentalhost\Vanilla\EventialsDriver\Types\WebinarType;

class EventialsDriverTest
    extends TestCase
{
    private static DriverHandler $driverHandler;

    private static function removeAllTestingWebinars(): void
    {
        // Remove all test-* webinar.
        foreach (self::$driverHandler->getWebinars() as $webinar) {
            if (str_starts_with($webinar->slug, 'test-')) {
                self::$driverHandler->removeWebinar($webinar->id);
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::removeAllTestingWebinars();
    }

    private function getClientId(): ?string
    {
        return $_SERVER['EVENTIALS_CLIENT_ID'] ?? null;
    }

    private function getClientSecret(): ?string
    {
        return $_SERVER['EVENTIALS_CLIENT_SECRET'] ?? null;
    }

    private function getCredentials(): CredentialsType
    {
        $clientId     = $this->getClientId();
        $clientSecret = $this->getClientSecret();

        if (!$clientId || !$clientSecret) {
            $this->markTestSkipped('Eventials Client ID or Secret is not set');
        }

        return CredentialsType::create($clientId, $clientSecret);
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testSubscribeUserRequiredFieldsOnly
     */
    public function testAddWebinarParticipant(DriverHandler $driverHandler, WebinarType $webinar): WebinarParticipantType
    {
        $participantType = $driverHandler->addWebinarParticipant($webinar->id, 'required-only-fields@domain.com');

        self::assertSame('required-only-fields@domain.com', $participantType->email);
        self::assertFalse($participantType->send_invitation);
        self::assertFalse($participantType->invitation_sent);
        self::assertTrue($participantType->date_added->isToday());
        self::assertSame([], $participantType->additional_data);
        self::assertNull($participantType->access_code);
        self::assertStringEndsWith('?email=required-only-fields@domain.com', $participantType->access_url);

        return $participantType;
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testGetWebinarParticipant
     */
    public function testAddWebinarParticipantAgain(DriverHandler $driverHandler, WebinarType $webinar, WebinarParticipantType $previousParticipantType): WebinarParticipantType
    {
        $participantType = $driverHandler->addWebinarParticipant($webinar->id, 'required-only-fields@domain.com', '123456');

        self::assertSame($previousParticipantType->id, $participantType->id);
        self::assertSame('123456', $participantType->access_code);

        return $participantType;
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testRemoveWebinarParticipantAgain
     */
    public function testAddWebinarParticipantAgainAfterDelete(DriverHandler $driverHandler, WebinarType $webinar): WebinarParticipantType
    {
        return $this->testAddWebinarParticipant($driverHandler, $webinar);
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     */
    public function testGetWebinarForm(DriverHandler $driverHandler, WebinarType $webinar): void
    {
        $webinarFormTypeArray = $driverHandler->getWebinarForm($webinar->id);

        self::assertCount(10, $webinarFormTypeArray);
        self::assertFalse($webinarFormTypeArray->hasName('Invalid'));

        $webinarFormTypeInvalid = $webinarFormTypeArray->getByName('Invalid');

        self::assertNull($webinarFormTypeInvalid);

        $webinarFormTypeJob = $webinarFormTypeArray->getByName('Cargo');

        self::assertSame('Cargo', $webinarFormTypeJob->name);
        self::assertSame('text', $webinarFormTypeJob->type);
        self::assertFalse($webinarFormTypeJob->required);

        self::assertTrue($webinarFormTypeArray->hasName('Cargo'));
        self::assertTrue($webinarFormTypeArray->hasName('Telefone'));
        self::assertTrue($webinarFormTypeArray->hasName('Empresa'));
        self::assertTrue($webinarFormTypeArray->hasName('Estado'));
        self::assertTrue($webinarFormTypeArray->hasName('cidade'));
        self::assertTrue($webinarFormTypeArray->hasName('Estado_Registro'));
        self::assertTrue($webinarFormTypeArray->hasName('Registro_Profissional'));
        self::assertTrue($webinarFormTypeArray->hasName('Especialidade'));
        self::assertTrue($webinarFormTypeArray->hasName('Categoria'));
        self::assertTrue($webinarFormTypeArray->hasName('attendee_name'));

        self::assertSame('phone', $webinarFormTypeArray->getByName('Telefone')->type);
        self::assertSame('select', $webinarFormTypeArray->getByName('Estado')->type);

        self::assertTrue($webinarFormTypeArray->getByName('attendee_name')->required);
    }

    /**
     * @depends testWithCredentials
     * @depends testScheduleWebinar
     */
    public function testGetWebinarInformation(DriverHandler $driverHandler, WebinarType $webinar): WebinarType
    {
        $webinarResponse = $driverHandler->getWebinar($webinar->id);

        static::assertSame($webinar->id, $webinarResponse->id);

        static::assertStringStartsWith('test ', $webinarResponse->title);
        static::assertStringStartsWith('test ', $webinarResponse->description);
        static::assertStringStartsWith('test-', $webinarResponse->slug);

        static::assertSame(WebinarType::STATE_UPCOMING, $webinarResponse->state);

        static::assertTrue($webinarResponse->start_time->isTomorrow());
        static::assertTrue($webinarResponse->date_added->isToday());

        static::assertFalse($webinarResponse->is_public);
        static::assertFalse($webinarResponse->is_group_only);
        static::assertFalse($webinarResponse->is_draft);

        static::assertSame(372, $webinarResponse->subscription_form_id);

        static::assertSame(0, $webinarResponse->total_invited);
        static::assertSame(0, $webinarResponse->total_subscribed);

        static::assertSame(0.0, (float) $webinarResponse->ticket_price);

        static::assertFalse($webinarResponse->embed_enabled);

        static::assertSame(16, strlen($webinarResponse->playback_stream_name));
        static::assertSame(16, strlen($webinarResponse->publish_stream_name));

        static::assertFalse($webinarResponse->live_scheduled);
        static::assertFalse($webinarResponse->block_shared_subscriptions);
        static::assertSame('', $webinarResponse->access_code);

        static::assertStringStartsWith('https://www.eventials.com/', $webinarResponse->url);
        static::assertStringStartsWith('https://custom-domain.com/', $webinarResponse->getCustomDomainUrl('custom-domain.com'));
        static::assertStringContainsString('/test-', $webinarResponse->url);
        static::assertStringContainsString('/test-', $webinarResponse->getCustomDomainUrl('custom-domain.com'));
        static::assertStringContainsString('https://api.eventials.com/v1/webinars/', $webinarResponse->cover);

        static::assertInstanceOf(WebinarOwnerType::class, $webinarResponse->owner);

        static::assertInstanceOf(WebinarTimezoneType::class, $webinarResponse->timezone);
        static::assertSame(69, $webinarResponse->timezone->id);
        static::assertSame('BR', $webinarResponse->timezone->country_isocode);
        static::assertSame('America/Sao_Paulo', $webinarResponse->timezone->code);
        static::assertTrue($webinarResponse->timezone->is_default);
        static::assertSame('', $webinarResponse->timezone->name);
        static::assertSame(-10800, $webinarResponse->timezone->utc_offset);

        static::assertInstanceOf(WebinarCategoryType::class, $webinarResponse->category);
        static::assertSame(23, $webinarResponse->category->id);
        static::assertSame('Other', $webinarResponse->category->name);
        static::assertSame('other', $webinarResponse->category->slug);

        static::assertInstanceOf(WebinarStreamType::class, $webinarResponse->stream);
        static::assertSame(16, strlen($webinarResponse->stream->key));
        static::assertSame(16, strlen($webinarResponse->stream_key));
        static::assertSame($webinarResponse->stream->key, $webinarResponse->stream_key);
        static::assertSame('rtmp://awslive.eventials.com/eventialsLiveOrigin', $webinarResponse->stream->url);
        static::assertSame('rtmp://awslive.eventials.com/eventialsLiveOrigin', $webinarResponse->stream_url);
        static::assertSame('-', $webinarResponse->stream->token);

        return $webinarResponse;
    }

    /**
     * @depends testWithCredentials
     * @depends testScheduleWebinar
     */
    public function testGetWebinarInformationWithUrl(DriverHandler $driverHandler, WebinarType $webinar): void
    {
        $webinarResponse = $driverHandler->getWebinarWithUrl($webinar->url);

        static::assertSame($webinar->id, $webinarResponse->id);
    }

    /**
     * @depends testWithCredentials
     * @depends testScheduleWebinar
     */
    public function testGetWebinarInvalid(DriverHandler $driverHandler): void
    {
        static::assertNull($driverHandler->getWebinar(123));
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testAddWebinarParticipant
     */
    public function testGetWebinarParticipant(DriverHandler $driverHandler, WebinarType $webinar): WebinarParticipantType
    {
        $participantsTypeArray = $driverHandler->getWebinarParticipants($webinar->id);

        self::assertCount(1, $participantsTypeArray);
        self::assertNull($participantsTypeArray->getByEmail('inexistent-participant@domain.com'));

        return $participantsTypeArray->getByEmail('required-only-fields@domain.com');
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testAddWebinarParticipantAgain
     */
    public function testRemoveWebinarParticipant(DriverHandler $driverHandler, WebinarType $webinar, WebinarParticipantType $participantType): void
    {
        $driverHandler->removeWebinarParticipant($webinar->id, $participantType->id);

        $participantsTypeArray = $driverHandler->getWebinarParticipants($webinar->id);

        self::assertCount(0, $participantsTypeArray);
        self::assertNull($participantsTypeArray->getByEmail('inexistent-participant@domain.com'));
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testAddWebinarParticipantAgain
     * @depends testRemoveWebinarParticipant
     */
    public function testRemoveWebinarParticipantAgain(DriverHandler $driverHandler, WebinarType $webinar, WebinarParticipantType $participantType): void
    {
        $this->testRemoveWebinarParticipant($driverHandler, $webinar, $participantType);
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testAddWebinarParticipantAgainAfterDelete
     */
    public function testRemoveWebinarParticipantWithEmail(DriverHandler $driverHandler, WebinarType $webinar, WebinarParticipantType $participantType): void
    {
        $driverHandler->removeWebinarParticipantWithEmail($webinar->id, $participantType->email);

        $participantsTypeArray = $driverHandler->getWebinarParticipants($webinar->id);

        self::assertCount(0, $participantsTypeArray);
        self::assertNull($participantsTypeArray->getByEmail('inexistent-participant@domain.com'));
    }

    /**
     * @depends testWithCredentials
     */
    public function testScheduleWebinar(DriverHandler $driverHandler): WebinarType
    {
        $webinarTitle = 'test ' . Carbon::now()->toIso8601ZuluString('microsecond');

        $webinarRequest = WebinarRequestType::create($webinarTitle, Carbon::now()->addDay(), null, 372);
        $webinar        = $driverHandler->scheduleWebinar($webinarRequest);

        static::assertSame($webinarTitle, $webinar->title);
        static::assertStringStartsWith('test-', $webinar->slug);

        return $webinar;
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testSubscribeUserRequiredFieldsOnly
     */
    public function testSubscribeUserAllFields(DriverHandler $driverHandler, WebinarType $webinar): void
    {
        $subscriber = WebinarSubscriberType::create('all-fields@domain.com', [
            'attendee_name'         => 'All Fields',
            'Cargo'                 => 'Diretor',
            'Telefone'              => '(21) 99999-9999',
            'Empresa'               => 'Test Company',
            'Estado'                => 'RJ',
            'cidade'                => 'Test City',
            'Estado_Registro'       => 'Rio de Janeiro',
            'Registro_Profissional' => 'CTF 123456/RJ',
            'Especialidade'         => 'Testologista',
            'Categoria'             => 'Testador'
        ]);

        $driverHandler->subscribeUser($webinar->id, $subscriber);

        $listSubscriptions = $driverHandler->getSubscribedUsers($webinar->id);

        self::assertCount(2, $listSubscriptions);
        self::assertNull($listSubscriptions->getByEmail('non-registered@domain.com'));

        $requiredOnlyFieldsSubscriptionUser = $listSubscriptions->getByEmail('all-fields@domain.com');

        self::assertTrue($requiredOnlyFieldsSubscriptionUser->date_added->isToday());
        self::assertTrue($requiredOnlyFieldsSubscriptionUser->share_info);

        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->id);
        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->username);
        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->first_name);
        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->last_name);
        self::assertSame('all-fields@domain.com', $requiredOnlyFieldsSubscriptionUser->user->email);

        /** @var CustomSubscriptionFormType $requiredOnlyFieldsSubscriptionUserCustomFields */
        $requiredOnlyFieldsSubscriptionUserCustomFields = $requiredOnlyFieldsSubscriptionUser->custom_fields;

        self::assertSame('All Fields', $requiredOnlyFieldsSubscriptionUserCustomFields->attendee_name);
        self::assertSame('Diretor', $requiredOnlyFieldsSubscriptionUserCustomFields->cargo);
        self::assertSame('Testador', $requiredOnlyFieldsSubscriptionUserCustomFields->categoria);
        self::assertSame('Test City', $requiredOnlyFieldsSubscriptionUserCustomFields->cidade);
        self::assertSame('Test Company', $requiredOnlyFieldsSubscriptionUserCustomFields->empresa);
        self::assertSame('Testologista', $requiredOnlyFieldsSubscriptionUserCustomFields->especialidade);
        self::assertSame('RJ', $requiredOnlyFieldsSubscriptionUserCustomFields->estado);
        self::assertSame('Rio de Janeiro', $requiredOnlyFieldsSubscriptionUserCustomFields->estado_registro);
        self::assertSame('CTF 123456/RJ', $requiredOnlyFieldsSubscriptionUserCustomFields->registro_profissional);
        self::assertSame('(21)999999999', $requiredOnlyFieldsSubscriptionUserCustomFields->telefone);
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testGetWebinarForm
     */
    public function testSubscribeUserRequiredFieldsOnly(DriverHandler $driverHandler, WebinarType $webinar): void
    {
        $subscriber = WebinarSubscriberType::create('required-only-fields@domain.com', [
            'attendee_name' => 'Required Fields Only'
        ]);

        $driverHandler->subscribeUser($webinar->id, $subscriber);

        $listSubscriptions = $driverHandler->getSubscribedUsers($webinar->id);

        self::assertCount(1, $listSubscriptions);
        self::assertNull($listSubscriptions->getByEmail('non-registered@domain.com'));

        $requiredOnlyFieldsSubscriptionUser = $listSubscriptions->getByEmail('required-only-fields@domain.com');

        self::assertTrue($requiredOnlyFieldsSubscriptionUser->date_added->isToday());
        self::assertTrue($requiredOnlyFieldsSubscriptionUser->share_info);

        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->id);
        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->username);
        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->first_name);
        self::assertNull($requiredOnlyFieldsSubscriptionUser->user->last_name);
        self::assertSame('required-only-fields@domain.com', $requiredOnlyFieldsSubscriptionUser->user->email);

        /** @var CustomSubscriptionFormType $requiredOnlyFieldsSubscriptionUserCustomFields */
        $requiredOnlyFieldsSubscriptionUserCustomFields = $requiredOnlyFieldsSubscriptionUser->custom_fields;

        self::assertSame('Required Fields Only', $requiredOnlyFieldsSubscriptionUserCustomFields->attendee_name);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->cargo);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->categoria);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->cidade);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->empresa);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->especialidade);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->estado);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->estado_registro);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->registro_profissional);
        self::assertNull($requiredOnlyFieldsSubscriptionUserCustomFields->telefone);
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     * @depends testSubscribeUserRequiredFieldsOnly
     */
    public function testSubscribeUserRequiredFieldsOnlyAgain(DriverHandler $driverHandler, WebinarType $webinar): void
    {
        $subscriber = WebinarSubscriberType::create('required-only-fields@domain.com', [
            'attendee_name' => 'Required Fields Only'
        ]);

        $driverHandler->subscribeUser($webinar->id, $subscriber);

        self::assertTrue(true);
    }

    /**
     * @depends testWithCredentials
     * @depends testGetWebinarInformation
     */
    public function testUpdateWebinar(DriverHandler $driverHandler, WebinarType $webinar): void
    {
        $webinarRequest              = new WebinarRequestType;
        $webinarRequest->description = 'test modified';

        $driverHandler->updateWebinar($webinar->id, $webinarRequest);

        $webinarResponse = $driverHandler->getWebinar($webinar->id);

        static::assertSame($webinar->id, $webinarResponse->id);
        static::assertSame('test modified', $webinarResponse->description);
    }

    public function testWithCredentials(): DriverHandler
    {
        self::$driverHandler = DriverConnector::withCredentials($this->getCredentials());

        self::removeAllTestingWebinars();

        static::assertTrue(true);

        return self::$driverHandler;
    }
}
