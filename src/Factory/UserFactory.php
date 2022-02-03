<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UploadHelper;
use Doctrine\Migrations\Configuration\Exception\FileNotFound;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<User>
 *
 * @method static User|Proxy createOne(array $attributes = [])
 * @method static User[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static User|Proxy find(object|array|mixed $criteria)
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy first(string $sortedField = 'id')
 * @method static User|Proxy last(string $sortedField = 'id')
 * @method static User|Proxy random(array $attributes = [])
 * @method static User|Proxy randomOrCreate(array $attributes = [])
 * @method static User[]|Proxy[] all()
 * @method static User[]|Proxy[] findBy(array $attributes)
 * @method static User[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static User[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static UserRepository|RepositoryProxy repository()
 * @method User|Proxy create(array|callable $attributes = [])
 */
final class UserFactory extends ModelFactory
{


    private $hasher;
    private UploadHelper $uploadHelper;

    public function __construct(UserPasswordHasherInterface $hasher, UploadHelper $uploadHelper)
    {
        parent::__construct();
        $this->hasher = $hasher;
        $this->uploadHelper = $uploadHelper;
    }

    protected function getDefaults(): array
    {
        $randomImage = array_diff(scandir(__DIR__ . '/images/user/'), array('.','..'));

        return [
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'votes'=> rand(-500,500),
            'profilePicture'=> $this->uploadHelper->fixtureUpload(
                new File(__DIR__ . '/images/user/' . self::faker()->randomElement($randomImage)), 'user'
            )
        ];
    }

    protected function initialize(): self
    {
       return $this->afterInstantiate(function (User $user) {
           $email = strtolower($user->getFirstName()) . '.' .strtolower($user->getLastName()) . '@gmail.com';
           $user->setEmail($email);
           $user->setPassword($this->hasher->hashPassword($user, 'password'));
       });
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
