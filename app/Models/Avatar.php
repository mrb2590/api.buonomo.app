<?php

namespace App\Models;

use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    use HasUuid;

    /**
     * The base url for fetching the avatar svg.
     *
     * @var string
     */
    protected $baseUrl = 'https://avataaars.io';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'avatar_style',
        'accessories_type',
        'clothe_type',
        'clothe_color',
        'graphic_type',
        'eyebrow_type',
        'eye_type',
        'facial_hair_type',
        'facial_hair_color',
        'hair_color',
        'mouth_type',
        'skin_color',
        'top_type',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
    ];

    /**
     * The avatar styles.
     *
     * @var array
     */
    public static $avatarStyles = [
        'Circle',
        'Transparant',
    ];

    /**
     * The accessories types.
     *
     * @var array
     */
    public static $accessoriesTypes = [
        'Blank',
        'Kurt',
        'Prescription01',
        'Prescription02',
        'Round',
        'Sunglasses',
        'Wayfarers',
    ];

    /**
     * The clothe types.
     *
     * @var array
     */
    public static $clotheTypes = [
        'BlazerShirt',
        'BlazerSweater',
        'CollarSweater',
        'GraphicShirt',
        'Hoodie',
        'Overall',
        'ShirtCrewNeck',
        'ShirtScoopNeck',
        'ShirtVNeck',
    ];

    /**
     * The clothe colors.
     *
     * @var array
     */
    public static $clotheColors = [
        'Black',
        'Blue01',
        'Blue02',
        'Blue03',
        'Gray01',
        'Gray02',
        'Heather',
        'PastelBlue',
        'PastelGreen',
        'PastelOrange',
        'PastelRed',
        'PastelYellow',
        'Pink',
        'Red',
        'White',
    ];

    /**
     * The graphic types.
     *
     * @var array
     */
    public static $graphicTypes = [
        'Bat',
        'Cumbia',
        'Deer',
        'Diamond',
        'Hola',
        'Pizza',
        'Resist',
        'Selena',
        'Bear',
        'SkullOutline',
        'Skull',
    ];

    /**
     * The eyebrow types.
     *
     * @var array
     */
    public static $eyebrowTypes = [
        'Angry',
        'AngryNatural',
        'Default',
        'DefaultNatural',
        'FlatNatural',
        'RaisedExcited',
        'RaisedExcitedNatural',
        'SadConcerned',
        'SadConcernedNatural',
        'UnibrowNatural',
        'UpDown',
        'UpDownNatural',
    ];

    /**
     * The eye types.
     *
     * @var array
     */
    public static $eyeTypes = [
        'Close',
        'Cry',
        'Default',
        'Dizzy',
        'EyeRoll',
        'Happy',
        'Hearts',
        'Side',
        'Squint',
        'Surprised',
        'Wink',
        'WinkWacky',
    ];

    /**
     * The facial hair types.
     *
     * @var array
     */
    public static $facialHairTypes = [
        'Blank',
        'BeardMedium',
        'BeardLight',
        'BeardMagestic',
        'MoustacheFancy',
        'MoustacheMagnum',
    ];

    /**
     * The facial hair colors.
     *
     * @var array
     */
    public static $facialHairColors = [
        'Auburn',
        'Black',
        'Blonde',
        'BlondeGolden',
        'Brown',
        'BrownDark',
        'Platinum',
        'Red',
    ];

    /**
     * The hair colors.
     *
     * @var array
     */
    public static $hairColors = [
        'Auburn',
        'Black',
        'Blonde',
        'BlondeGolden',
        'Brown',
        'BrownDark',
        'PastelPink',
        'Platinum',
        'Red',
        'SilverGray',
    ];

    /**
     * The mouth types.
     *
     * @var array
     */
    public static $mouthTypes = [
        'Concerned',
        'Default',
        'Disbelief',
        'Eating',
        'Grimace',
        'Sad',
        'ScreamOpen',
        'Serious',
        'Smile',
        'Tongue',
        'Twinkle',
        'Vomit',
    ];

    /**
     * The skin colors.
     *
     * @var array
     */
    public static $skinColors = [
        'Tanned',
        'Yellow',
        'Pale',
        'Light',
        'Brown',
        'DarkBrown',
        'Black',
    ];

    /**
     * The top types.
     *
     * @var array
     */
    public static $topTypes = [
        'NoHair',
        'Eyepatch',
        'Hat',
        'Hijab',
        'Turban',
        'WinterHat1',
        'WinterHat2',
        'WinterHat3',
        'WinterHat4',
        'LongHairBigHair',
        'LongHairBob',
        'LongHairBun',
        'LongHairCurly',
        'LongHairCurvy',
        'LongHairDreads',
        'LongHairFrida',
        'LongHairFro',
        'LongHairFroBand',
        'LongHairNotTooLong',
        'LongHairShavedSides',
        'LongHairMiaWallace',
        'LongHairStraight',
        'LongHairStraight2',
        'LongHairStraightStrand',
        'ShortHairDreads01',
        'ShortHairDreads02',
        'ShortHairFrizzle',
        'ShortHairShaggyMullet',
        'ShortHairShortCurly',
        'ShortHairShortFlat',
        'ShortHairShortRound',
        'ShortHairShortWaved',
        'ShortHairSides',
        'ShortHairTheCaesar',
        'ShortHairTheCaesarSidePart',
    ];

    /**
     * Create a random avatar.
     */
    public static function random()
    {
        $rand = function ($var) {
            return Avatar::${$var}[mt_rand(0, count(Avatar::${$var}) - 1)];
        };

        $avatar = new Avatar;
        $avatar->avatar_style = $rand('avatarStyles');
        $avatar->accessories_type = $rand('accessoriesTypes');
        $avatar->clothe_type = $rand('clotheTypes');
        $avatar->clothe_color = $rand('clotheColors');
        $avatar->graphic_type = $rand('graphicTypes');
        $avatar->eyebrow_type = $rand('eyebrowTypes');
        $avatar->eye_type = $rand('eyeTypes');
        $avatar->facial_hair_type = $rand('facialHairTypes');
        $avatar->facial_hair_color = $rand('facialHairColors');
        $avatar->hair_color = $rand('hairColors');
        $avatar->mouth_type = $rand('mouthTypes');
        $avatar->skin_color = $rand('skinColors');
        $avatar->top_type = $rand('topTypes');

        return $avatar;
    }

    /**
     * Get the avatar url.
     *
     * @return string
     */
    protected function getUrlAttribute()
    {
        $url = $this->baseUrl.'?';
        $url .= 'avatarStyle='.urlencode($this->avatar_style).'&';
        $url .= 'accessoriesType='.urlencode($this->accessories_type).'&';
        $url .= 'clotheType='.urlencode($this->clothe_type).'&';
        $url .= 'clotheColor='.urlencode($this->clothe_color).'&';
        $url .= 'graphicType='.urlencode($this->graphic_type).'&';
        $url .= 'eyebrowType='.urlencode($this->eyebrow_type).'&';
        $url .= 'eyeType='.urlencode($this->eye_type).'&';
        $url .= 'facialHairColor='.urlencode($this->facial_hair_color).'&';
        $url .= 'facialHairType='.urlencode($this->facial_hair_type).'&';
        $url .= 'hairColor='.urlencode($this->hair_color).'&';
        $url .= 'mouthType='.urlencode($this->mouth_type).'&';
        $url .= 'skinColor='.urlencode($this->skin_color).'&';
        $url .= 'topType='.urlencode($this->top_type);

        return $url;
    }

    /**
     * Get the user this avatar belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
