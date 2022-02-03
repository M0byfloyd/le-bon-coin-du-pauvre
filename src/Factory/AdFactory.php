<?php

namespace App\Factory;

use App\Entity\Ad;
use App\Repository\AdRepository;
use App\Service\UploadHelper;
use Symfony\Component\HttpFoundation\File\File;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Ad>
 *
 * @method static Ad|Proxy createOne(array $attributes = [])
 * @method static Ad[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Ad|Proxy find(object|array|mixed $criteria)
 * @method static Ad|Proxy findOrCreate(array $attributes)
 * @method static Ad|Proxy first(string $sortedField = 'id')
 * @method static Ad|Proxy last(string $sortedField = 'id')
 * @method static Ad|Proxy random(array $attributes = [])
 * @method static Ad|Proxy randomOrCreate(array $attributes = [])
 * @method static Ad[]|Proxy[] all()
 * @method static Ad[]|Proxy[] findBy(array $attributes)
 * @method static Ad[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Ad[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static AdRepository|RepositoryProxy repository()
 * @method Ad|Proxy create(array|callable $attributes = [])
 */
final class AdFactory extends ModelFactory
{
    private UploadHelper $uploadHelper;

    public function __construct(UploadHelper $uploadHelper)
    {
        parent::__construct();

        $this->uploadHelper = $uploadHelper;
    }

    protected function getDefaults(): array
    {
        $randomImage = array_diff(scandir(__DIR__ . '/images/ad/'), array('.','..'));

        return [
            'title' => self::faker()->realText(40),
            'description'=> self::faker()->paragraph(rand(1,4), true),
            'price'=> rand(0,1000),
            'votes'=> rand(-500,500),
            'creationDate'=> self::faker()->dateTime(),
            'images'=> $this->uploadHelper->fixtureUpload(
                new File(__DIR__ . '/images/ad/' . self::faker()->randomElement($randomImage)), 'ad'
            )
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Ad $ad): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Ad::class;
    }
}
