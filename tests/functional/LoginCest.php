<?php namespace Platform;

use DateTime;
use Gzero\Entity\User;
use Illuminate\Contracts\Mail\Mailer;
use Mockery as m;

class LoginCest {

    // tests
    public function register(FunctionalTester $I)
    {
        $I->wantTo('register a user');

        $I->amOnPage('/en/register');
        $I->fillField('nick', 'nick');
        $I->fillField('first_name', 'John');
        $I->fillField('last_name', 'Doe');
        $I->fillField('email', 'example@example.com');
        $I->fillField('password', 'password');
        $I->click('button[type=submit]');

        $I->amOnPage('/');
        $I->seeRecord(
            'users',
            [
                'email'      => 'example@example.com',
                'nick'       => 'nick',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ]
        );
        $I->seeAuthentication();
    }

    public function registerWithoutFirstAndLastName(FunctionalTester $I)
    {
        $I->wantTo('register a user without first and last name');
        $I->amOnPage('/en/register');
        $I->fillField('nick', 'nick');
        $I->fillField('email', 'example@example.com');
        $I->fillField('password', 'password');
        $I->click('button[type=submit]');

        $I->amOnPage('/');
        $I->seeRecord(
            'users',
            [
                'email'      => 'example@example.com',
                'nick'       => 'nick',
                'first_name' => null,
                'last_name'  => null,
            ]
        );
        $I->seeAuthentication();
    }

    public function registerAlreadyExistingUser(FunctionalTester $I)
    {
        $I->wantTo('prevent registration a user with already registered email');
        $I->haveRecord(
            'users',
            [
                'nick'       => 'nick',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john@doe.com',
                'password'   => bcrypt('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ]
        );

        $I->amOnPage('/en/register');
        $I->fillField('nick', 'nick');
        $I->fillField('first_name', 'John');
        $I->fillField('last_name', 'Doe');
        $I->fillField('email', 'john@doe.com');
        $I->fillField('password', 'password');
        $I->click('button[type=submit]');
        $I->see('The email has already been taken.');
        $I->see('The nick has already been taken.');
        $I->dontSeeAuthentication();
    }

    public function preventSpamUserRegistrations(FunctionalTester $I)
    {
        $I->wantTo('prevent a spammer users registrations');

        $I->amOnPage('/en/register');
        $I->fillField('nick', 'nick');
        $I->fillField('first_name', 'John');
        $I->fillField('last_name', 'Doe');
        $I->fillField('email', 'example@example.com');
        $I->fillField('password', 'password');
        $I->fillField('accountIntent', 'randomstring');
        $I->click('button[type=submit]');

        $I->amOnPage('/en');
        $I->seeResponseCodeIs(200);
        $I->dontSeeAuthentication();
    }

    public function seeWelcomePage(FunctionalTester $I)
    {
        $I->wantTo('register a user and see welcome page');

        // Mocking Mailer
        $mock = m::mock(Mailer::class);
        $I->haveSingleton(
            Mailer::class,
            function () use ($mock) {
                return $mock;
            }
        );

        $appName = $I->getApplication()->make('config')->get('app.name');

        $mock->shouldReceive('send')->once()
            ->with(
                'emails.auth.welcome',
                m::on(
                    function ($data) use ($I) {
                        $I->assertArrayHasKey('user', $data);
                        $I->assertInstanceOf(User::class, $data['user']);
                        return true;
                    }
                ),
                m::on(
                    function ($closure) use ($I, $appName) {
                        $message = m::mock();
                        $message->shouldReceive('to')
                            ->with('example@example.com', 'nick')
                            ->andReturn(m::self());
                        $message->shouldReceive('subject')
                            ->with("Welcome to $appName")
                            ->andReturn(m::self());
                        try {
                            $closure($message);
                        } catch (m\Exception $e) {
                            $I->fail($e->getMessage());
                        }
                        return true;
                    }
                )
            );

        $I->amOnPage('/en/register');
        $I->fillField('nick', 'nick');
        $I->fillField('first_name', 'John');
        $I->fillField('last_name', 'Doe');
        $I->fillField('email', 'example@example.com');
        $I->fillField('password', 'password');
        $I->click('button[type=submit]');

        $I->seeCurrentUrlEquals('/en/account/welcome?method=Signup+form');
        $I->see('Welcome!');
        $I->see('Your account was successfully created. Thank you for your registration!');
        $I->see('My Account');
        $I->see('Return to the homepage');
        $I->seeRecord(
            'users',
            [
                'email'      => 'example@example.com',
                'nick'       => 'nick',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ]
        );
        $I->seeAuthentication();
    }

    public function login(FunctionalTester $I)
    {
        $I->wantTo('login as a user');
        $I->haveRecord(
            'users',
            [
                'nick'       => 'Johny',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john@doe.com',
                'password'   => bcrypt('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ]
        );
        $I->amOnPage('/en/login');
        $I->fillField('email', 'john@doe.com');
        $I->fillField('password', 'password');
        $I->click('button[type=submit]');
        $I->seeCurrentUrlEquals('/en');
        $I->amOnPage('/en');
        $I->seeAuthentication();
        $I->see('Johny');

        // Trying to access admin panel
        $I->amOnPage('admin');
        $I->seePageNotFound();
    }

    public function loginNonExistingUser(FunctionalTester $I)
    {
        $I->wantTo('prevent login as a none existing user');
        $I->amOnPage('/en/login');
        $I->fillField('email', 'john@doe.com');
        $I->fillField('password', 'password');
        $I->click('button[type=submit]');
        $I->see('These credentials do not match our records.');
        $I->dontSeeAuthentication();
    }

    public function loginAsAdmin(FunctionalTester $I)
    {
        $I->wantTo('login as a admin');
        $I->loginAsAdmin();
        $I->seeAuthentication();
        $I->amOnPage('admin');
        $I->see('G-ZERO ADMIN');
    }

    public function logout(FunctionalTester $I)
    {
        $I->wantTo('logout as a user');
        $I->haveRecord(
            'users',
            [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john@doe.com',
                'password'   => bcrypt('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ]
        );
        $I->login('john@doe.com', 'password');
        $I->logout();
        $I->dontSeeAuthentication();
    }

    public function canRemindPassword(FunctionalTester $I)
    {
        $I->wantTo('remind a password');
        $I->amOnPage('/en/password/reset');
        $I->see('Reset Password');
        $I->see('Send password reset link');
    }

    public function canNotRemindPasswordAsLoggedUser(FunctionalTester $I)
    {
        $I->wantTo('remind a password as logged user');
        $I->haveRecord(
            'users',
            [
                'nick'       => 'JohnDoe',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john@doe.com',
                'password'   => bcrypt('password'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ]
        );
        $I->login('john@doe.com', 'password');
        $I->amOnPage('/en/password/reset');
        $I->see('JohnDoe');
    }
}
