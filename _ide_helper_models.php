<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $dues_type_id
 * @property string $dues_type_name
 * @property numeric $dues_type_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentModel> $payments
 * @property-read int|null $payments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel whereDuesTypeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel whereDuesTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel whereDuesTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DuesTypeModel whereUpdatedAt($value)
 */
	class DuesTypeModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $expense_id
 * @property string $expense_description
 * @property numeric $expense_amount
 * @property \Illuminate\Support\Carbon $expense_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel whereExpenseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel whereExpenseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel whereExpenseDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseModel whereUpdatedAt($value)
 */
	class ExpenseModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $house_id
 * @property string $house_name
 * @property string $house_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HouseOccupantModel> $houseOccupants
 * @property-read int|null $house_occupants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel whereHouseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel whereHouseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseModel whereUpdatedAt($value)
 */
	class HouseModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $house_occupant_id
 * @property int $house_id
 * @property int $occupant_id
 * @property \Illuminate\Support\Carbon $start_in_date
 * @property \Illuminate\Support\Carbon $end_in_date
 * @property bool $is_current
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\HouseModel $house
 * @property-read \App\Models\OccupantModel $occupant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentModel> $payments
 * @property-read int|null $payments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereEndInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereHouseOccupantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereIsCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereOccupantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereStartInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseOccupantModel whereUpdatedAt($value)
 */
	class HouseOccupantModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $occupant_id
 * @property string $occupant_name
 * @property string $occupant_ktp_photo
 * @property string $occupant_status
 * @property string $occupant_phone_number
 * @property bool $is_married
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $occupant_ktp_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HouseOccupantModel> $houseOccupants
 * @property-read int|null $house_occupants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentModel> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserModel> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereIsMarried($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereOccupantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereOccupantKtpPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereOccupantName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereOccupantPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereOccupantStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OccupantModel whereUpdatedAt($value)
 */
	class OccupantModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $payment_id
 * @property int $dues_type_id
 * @property int $payer_occupant_id
 * @property int $house_occupant_id
 * @property numeric $payment_amount
 * @property \Illuminate\Support\Carbon $payment_date
 * @property int $payment_period_month
 * @property int $payment_period_year
 * @property string|null $payment_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DuesTypeModel $duesType
 * @property-read \App\Models\HouseOccupantModel $houseOccupant
 * @property-read \App\Models\OccupantModel $payerOccupant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel whereDuesTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel whereHouseOccupantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel wherePayerOccupantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel wherePaymentPeriodMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel wherePaymentPeriodYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentModel whereUpdatedAt($value)
 */
	class PaymentModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $user_id
 * @property string $username
 * @property string $password
 * @property bool $is_rt
 * @property int|null $occupant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\OccupantModel|null $occupant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereIsRt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereOccupantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModel whereUsername($value)
 */
	class UserModel extends \Eloquent implements \PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject {}
}

