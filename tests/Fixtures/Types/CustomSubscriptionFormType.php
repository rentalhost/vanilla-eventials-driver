<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Tests\Fixtures\Types;

use Rentalhost\Vanilla\Type\Type;

/**
 * @property string      $attendee_name
 * @property string|null $cargo
 * @property string|null $categoria
 * @property string|null $cidade
 * @property string|null $empresa
 * @property string|null $especialidade
 * @property string|null $estado
 * @property string|null $estado_registro
 * @property string|null $registro_profissional
 * @property string|null $telefone
 */
class CustomSubscriptionFormType
    extends Type
{
}
