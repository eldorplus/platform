<?php namespace Platform;

use Faker\Factory;
use Gzero\Entity\Block;
use Gzero\Entity\Content;
use Gzero\Entity\File;
use Gzero\Entity\FileType;
use Gzero\Repository\BlockRepository;
use Gzero\Repository\ContentRepository;
use Gzero\Repository\FileRepository;
use Gzero\Repository\UserRepository;
use Gzero\Entity\User;
use Illuminate\Events\Dispatcher;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor {

    use _generated\FunctionalTesterActions;

    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @var ContentRepository
     */
    private $contentRepo;

    /**
     * @var BlockRepository
     */
    private $blockRepo;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * FunctionalTester constructor.
     *
     * @param \Codeception\Scenario $scenario
     */
    public function __construct(\Codeception\Scenario $scenario)
    {
        $this->faker       = Factory::create();
        $this->contentRepo = new ContentRepository(
            new Content(),
            new Dispatcher()
        );
        $this->blockRepo   = new BlockRepository(
            new Block(), new Dispatcher()
        );
        $this->userRepo    = new UserRepository(new User(), new Dispatcher());
        parent::__construct($scenario);
    }

    /**
     * Login in to page
     *
     * @param $email
     * @param $password
     */
    public function login($email, $password)
    {
        $I = $this;
        $I->amOnPage('/en/login');
        $I->fillField('email', $email);
        $I->fillField('password', $password);
        $I->click('button[type=submit]');
        $I->amOnPage('/en');
        $I->seeAuthentication();
    }

    /**
     * Login as admin in to page
     */
    public function loginAsAdmin()
    {
        $I = $this;
        $I->amOnPage('/en/login');
        $I->fillField('email', 'admin@gzero.pl');
        $I->fillField('password', 'test');
        $I->click('button[type=submit]');
        $I->seeAuthentication();
    }

    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return User
     */
    public function haveUser($attributes = [])
    {
        $fakeAttributes = [
            'nick'       => $this->faker->userName,
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->email,
            'password'   => bcrypt('test123')
        ];

        $fakeAttributes = array_merge($fakeAttributes, $attributes);

        return $this->userRepo->create($fakeAttributes);
    }

    /**
     * Create content and return entity
     *
     * @param bool|false $attributes
     * @param null       $user
     *
     * @return Content
     */
    public function haveContent($attributes = false, $user = null)
    {
        $fakeAttributes = [
            'type'         => ['category', 'content'][rand(0, 1)],
            'is_active'    => 1,
            'published_at' => date('Y-m-d H:i:s'),
            'translations' => [
                'lang_code'       => 'en',
                'title'           => $this->faker->realText(38, 1),
                'teaser'          => '<p>' . $this->faker->realText(300) . '</p>',
                'body'            => $this->faker->realText(1000),
                'seo_title'       => $this->faker->realText(60, 1),
                'seo_description' => $this->faker->realText(160, 1),
                'is_active'       => rand(0, 1)
            ]
        ];

        $fakeAttributes = array_merge($fakeAttributes, $attributes);

        return $this->contentRepo->create($fakeAttributes, $user);
    }

    /**
     * Create block and return entity
     *
     * @param bool|false $attributes
     * @param null       $user
     *
     * @return Block
     */
    public function haveBlock($attributes = false, $user = null)
    {
        $fakeAttributes = [
            'type'         => 'basic',
            'region'       => 'header',
            'weight'       => 1,
            'filter'       => [],
            'options'      => [],
            'is_active'    => true,
            'is_cacheable' => true,
            'translations' => [
                'lang_code' => 'en',
                'title'     => $this->faker->realText(38, 1),
                'body'      => $this->faker->realText(1000),
            ]
        ];

        $fakeAttributes = array_merge($fakeAttributes, $attributes);

        return $this->blockRepo->create($fakeAttributes, $user);
    }
}
