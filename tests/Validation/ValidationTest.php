<?php
namespace Colibri\Tests\Validation;

use Colibri\Util\Str;
use Colibri\Validation\Validation;
use Colibri\Validation\ValidationException;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    private function assertScopeEquals(array $expected, Validation $validation)
    {
        $reflectionObject = new \ReflectionObject($validation);
        $scopeProperty    = $reflectionObject->getProperty('scope');
        $scopeProperty->setAccessible(true);

        $this->assertEquals($expected, $scopeProperty->getValue($validation));
    }

    /**
     * @covers \Colibri\Validation\Validation::setScope
     */
    public function testSetScope()
    {
        $fixtureScope = ['name' => 'Zheka', 'age' => '2y 10m', 'money' => 100];
        $validation   = new Validation(['someOldKey' => 'someOldValue']);
        $validation->setScope($fixtureScope);
        $this->assertScopeEquals($fixtureScope, $validation);
    }

    /**
     * @covers \Colibri\Validation\Validation::forScope
     * @return Validation
     */
    public function testForScope()
    {
        return Validation::forScope([
            'name'     => 'Василий',
            'lastName' => 'Попов',
            'email'    => 'vasyok.popov@gmail.com',
        ]);
    }

    /**
     * @covers  \Colibri\Validation\Validation::extendScope
     * @depends testForScope
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testExtendScope(Validation $validation)
    {
        $validation->extendScope([
            'id' => 44,
        ]);

        $this->assertScopeEquals(
            [
                'name'     => 'Василий',
                'lastName' => 'Попов',
                'email'    => 'vasyok.popov@gmail.com',
                'id'       => 44,
            ],
            $validation
        );

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::required
     * @depends testExtendScope
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testRequired(Validation $validation)
    {
        $validation
            ->required('name')
            ->required(['lastName', 'email', 'id'])
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->required('not-exists');
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals(sprintf(Validation::$requiredMessage, 'not-exists'), $validation->errors['not-exists']);

        $validation
            ->required(['name', 'not-exists-2'], 'field %s required');
        $this->assertEquals(2, count($validation->errors));
        $this->assertEquals('field not-exists-2 required', $validation->errors['not-exists-2']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::minLength
     * @depends testRequired
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testMinLength(Validation $validation)
    {
        $validation
            ->minLength('name', 6)
            ->minLength('not-exists', 2)
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->minLength('lastName', 6)
            ->minLength('not-exists', 2)
        ;
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals(sprintf(Validation::$minLengthMessage, 'lastName', 6), $validation->errors['lastName']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::maxLength
     * @depends testMinLength
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testMaxLength(Validation $validation)
    {
        $validation
            ->maxLength('lastName', 6)
            ->maxLength('not-exists', 2)
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->maxLength('name', 6);
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals(sprintf(Validation::$maxLengthMessage, 'name', 6), $validation->errors['name']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::regex
     * @depends testMaxLength
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testRegex(Validation $validation)
    {
        $validation
            ->regex('lastName', '/[а-яё]+/')
            ->regex('not-exists', '/[а-яё]+/')
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->regex('name', '/[a-z]+/', 'поле \'%s\' должно содержать только латинские буквы.');
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals('поле \'name\' должно содержать только латинские буквы.', $validation->errors['name']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::isIntGt0
     * @depends testRegex
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testIsIntGt0(Validation $validation)
    {
        $validation
            ->isIntGt0('id')
            ->isIntGt0('not-exists')
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->extendScope(['negativeInt' => -7, 'zeroInt' => 0])
            ->isIntGt0('name')
            ->isIntGt0('negativeInt', 'поле \'%s\' должно быть положительным')
            ->isIntGt0('zeroInt', 'error in field zeroInt')
        ;
        $this->assertEquals(3, count($validation->errors));
        $this->assertEquals(sprintf(Validation::$isIntGt0Message, 'name'), $validation->errors['name']);
        $this->assertEquals('поле \'negativeInt\' должно быть положительным', $validation->errors['negativeInt']);
        $this->assertEquals('error in field zeroInt', $validation->errors['zeroInt']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::isJSON
     * @depends testIsIntGt0
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testIsJSON(Validation $validation)
    {
        $validation
            ->extendScope(['json' => '{"some":11,"valid":"json"}'])
            ->isJSON('json')
            ->isJSON('not-exists')
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->extendScope(['not-json' => '{"some":11,"valid":"json"'])
            ->isJSON('not-json')
        ;
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals(sprintf(Validation::$isJSONMessage, 'not-json'), $validation->errors['not-json']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::isEmail
     * @depends testIsJSON
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testIsEmail(Validation $validation)
    {
        $validation
            ->extendScope(['email' => 'some-valid.email@domain.ru'])
            ->isEmail('email')
            ->isEmail('not-exists')
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->extendScope(['not-email' => 'some-not valid.email@domain.ru'])
            ->isEmail('not-email')
        ;
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals(sprintf(Validation::$isEmailMessage, 'not-email'), $validation->errors['not-email']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::isEqual
     * @depends testIsEmail
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testIsEqual(Validation $validation)
    {
        $validation
            ->extendScope(['one' => 'some-value', 'two' => 'some-value'])
            ->isEqual(['one', 'two'])
            ->isEqual(['one', 'not-exists'])
            ->isEqual(['not-exists', 'not-exists2'])
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->extendScope(['one' => 'some-value', 'two' => 'some-another-value'])
            ->isEqual(['one', 'two'])
        ;
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals(sprintf(Validation::$isEqualMessage, 'one\', \'two'), $validation->errors['two']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::is
     * @depends testIsEqual
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testIs(Validation $validation)
    {
        $validation
            ->extendScope(['message' => 'some text, that contains [cut] bb tag'])
            ->is(function ($value) {
                return Str::contains($value, '[cut]');
            }, 'message', 'field \'message\' must contains "[cut]" bb tag')
            ->is('self::theFalse', 'not-exists', 'something wrong')
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->extendScope(['no-cut-message' => 'some text, that does not contains "cut" bb tag'])
            ->is(function ($value) {
                return Str::contains($value, '[cut]');
            }, 'no-cut-message', 'no [cut] in field \'%s\'')
        ;
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals('no [cut] in field \'no-cut-message\'', $validation->errors['no-cut-message']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::isNot
     * @depends testIs
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testIsNot(Validation $validation)
    {
        $validation
            ->extendScope(['login' => 'vpopov'])
            ->isNot('\Colibri\Util\Str::isEmail', 'login', 'field \'login\' must not be a mail')
            ->isNot('self::theFalse', 'not-exists', 'something wrong')
        ;
        $this->assertEquals(0, count($validation->errors));

        $validation
            ->extendScope(['no-cut-message' => 'some text, that does not contains "cut" bb tag'])
            ->isNot(function ($value) {
                return ! Str::isEmail($value);
            }, 'login', 'field \'%s\' must be a mail')
        ;
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals('field \'login\' must be a mail', $validation->errors['login']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @return bool false
     */
    protected static function theFalse()
    {
        return false;
    }

    /**
     * @covers  \Colibri\Validation\Validation::addError
     * @depends testIsNot
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testAddError(Validation $validation)
    {
        $validation
            ->addError('additionalError', 'You are not filled anything.');
        $this->assertEquals(1, count($validation->errors));
        $this->assertEquals('You are not filled anything.', $validation->errors['additionalError']);

        $validation->errors = [];

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::valid
     * @depends testAddError
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testValid(Validation $validation)
    {
        $validation
            ->required(['name', 'lastName', 'email'])
            ->isEmail(['email', 'not-exists'])
            ->maxLength(['name', 'not-exists'], 8)
            ->minLength(['name', 'not-exists'], 4)
            ->isIntGt0(['id', 'not-exists'])
        ;
        $this->assertEquals(true, $validation->valid());

        $validation->required('not-exists');
        $this->assertEquals(false, $validation->valid());

        // not clear errors because in next method (testIfNotValid) we asserting that $validation is not valid
        //no $valida.->err = array();

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::ifNotValid
     * @depends testValid
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     */
    public function testIfNotValid(Validation $validation)
    {
        $someActions  = 0;
        $incomeErrors = [];
        $validation
            ->ifNotValid(function (array $errors) use (&$someActions, &$incomeErrors) {
                $someActions++;
                $incomeErrors = $errors;
            });
        $this->assertEquals(1, $someActions);
        $this->assertEquals($validation->errors, $incomeErrors);

        $validation->errors = [];
        $validation
            ->ifNotValid(function (array $errors) use (&$someActions, &$incomeErrors) {
                $someActions++;
                $incomeErrors = $errors;
            });
        $this->assertEquals(1, $someActions);
        $this->assertNotEquals([], $incomeErrors);

        // restore errors for next test method
        $validation->errors = $incomeErrors;

        return $validation;
    }

    /**
     * @covers  \Colibri\Validation\Validation::validate
     * @depends testIfNotValid
     *
     * @param Validation $validation
     *
     * @return \Colibri\Validation\Validation
     * @throws \Colibri\Validation\ValidationException
     */
    public function testValidate(Validation $validation)
    {
        $caught = 0;
        try {
            $validation->validate();
            $this->fail('no exception was thrown');
        } catch (ValidationException $exception) {
            $this->assertEquals($validation->errors, $exception->getErrors());
            $caught++;
        }
        $this->assertEquals(1, $caught);

        $validation->setScope([]); // new scope also clear the errors
        // we assert that there is no exception will thrown
        $validation->validate();

        return $validation;
    }
}
