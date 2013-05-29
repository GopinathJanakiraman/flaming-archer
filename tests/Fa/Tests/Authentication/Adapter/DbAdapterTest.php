<?php

namespace Fa\Tests\Authentication\Adapter;

use \DateTime;
use Fa\Authentication\Adapter\DbAdapter;
use Fa\Entity\User;
use Zend\Authentication\Result;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-11-13 at 07:43:23.
 */
class DbAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DbAdapter
     */
    protected $adapter;
    
    /**
     * @var Phpass\Hash
     */
    protected $hasher;

    /**
     * @var Fa\Dao\UserDao
     */
    protected $dao;
    
    /**
     * @var Fa\Entity\User
     */
    protected $user;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dao = $this->getMock('Fa\Dao\UserDao', array(), array(), '', false);
        $this->hasher = $this->getMock('Phpass\Hash');
        $this->user = new User(array(
            'id' => '1',
            'email' => 'user@example.com',
            'passwordHash' => '$2y$12$pZg9j8DBSIP2R/vfDzTQOeIt5n57r5VigCUl/HH.FrBOadi3YhdPS',
            'flickrApiKey' => '12341234',
            'lastLogin' => null,
        ));

        $this->adapter = new DbAdapter($this->dao, $this->hasher);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->adapter = null;
        parent::tearDown();
    }

    public function testCreation()
    {
        $this->assertInstanceOf('Fa\Authentication\Adapter\DbAdapter', $this->adapter);
    }

    public function testAuthenticateSuccess()
    {
        $this->dao->expects($this->once())
                ->method('findByEmailCanonical')
                ->with($this->user->getEmail())
                ->will($this->returnValue($this->user));

        $this->dao->expects($this->once())
                ->method('recordLogin')
                ->with($this->user->getEmail())
                ->will($this->returnValue(new DateTime('now')));
        
        $this->hasher->expects($this->once())
                ->method('checkPassword')
                ->with('password', $this->user->getPasswordHash())
                ->will($this->returnValue(true));
        
        $this->adapter->setCredentials($this->user->getEmail(), 'password');
        $result = $this->adapter->authenticate();
        
        $this->assertInstanceOf('Zend\Authentication\Result', $result);
        $this->assertSame($this->user, $result->getIdentity());
        $this->assertNull($this->user->getPasswordHash());
        $this->assertNull($this->user->getFlickrApiKey());
        $this->assertEquals(Result::SUCCESS, $result->getCode());
        $this->assertEquals(array(), $result->getMessages());
    }

    public function testAuthenticateFailure()
    {
        $this->dao->expects($this->once())
                ->method('findByEmailCanonical')
                ->with($this->user->getEmail())
                ->will($this->returnValue($this->user));
        
        $this->hasher->expects($this->once())
                ->method('checkPassword')
                ->with('badpassword', $this->user->getPasswordHash())
                ->will($this->returnValue(false));

        $this->adapter->setCredentials('user@example.com', 'badpassword');
        $result = $this->adapter->authenticate();

        $this->assertInstanceOf('Zend\Authentication\Result', $result);
        $this->assertEquals(array(), $result->getIdentity());
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
        $this->assertEquals(array('Invalid username or password provided'), $result->getMessages());
    }
    
    public function testAuthenticationUserNotFound()
    {
        $this->dao->expects($this->once())
                ->method('findByEmailCanonical')
                ->with('user@example.org')
                ->will($this->returnValue(false));
        
        $this->adapter->setCredentials('user@example.org', 'userdoesnotexist');
        $result = $this->adapter->authenticate();
        
        $this->assertInstanceOf('Zend\Authentication\Result', $result);
        $this->assertEquals(array(), $result->getIdentity());
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
        $this->assertEquals(array('Invalid username or password provided'), $result->getMessages());
    }

}
