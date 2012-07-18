<?php

/**
 * This file is part of the FOSRestByExample package.
 *
 * (c) Santiago Diaz <santiago.diaz@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace ByExample\DemoBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test class for user rest controller
 */
class UserRestControllerTest extends WebTestCase
{

    /**
     * Client
     * @var type
     */
    private $client;

    /**
     * Service Container  fos_user.user_manager
     * @var type
     */
    private $userManager;

    /**
     * Authentication header
     * @var type
     */
    private $header;

    /**
     * Test environment setup
     *
     * @return none
     */
    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->userManager = $kernel->getContainer()->get('fos_user.user_manager');
        $user = $this->userManager->findUserByUsername("usuario1");
        if (!$user) {
            $user = $this->userManager->createUser();
            $user->setUsername('usuario1');
            $user->setEmail('usuario1@bd.com');
            $user->setPlainPassword('12345');
            $user->setEnabled(true);
            $user->addRole('ROLE_PROFESSIONAL');
            $this->userManager->updateUser($user);
        }

        $user = $this->userManager->findUserByUsername("usuario2");
        if (!$user) {
            $user = $this->userManager->createUser();
            $user->setUsername('usuario2');
            $user->setEmail('usuario2@bd.com');
            $user->setPlainPassword('12345');
            $user->setEnabled(true);
            $user->addRole('ROLE_DEVELOPER');
            $this->userManager->updateUser($user);
        } else {
            $user->setPlainPassword('12345');
            $this->userManager->updateUser($user);
        }

        $user = $this->userManager->findUserByUsername("usuario3");
        if ($user) {
            $this->userManager->deleteUser($user);
        }


        /*
         * Creation of the client with the admin authenticated header
         */
        $user = $this->userManager->findUserByUsername("admin");
        if ($user) {
            $username = $user->getUsername();
            $password = $user->getPassword();
            $created = date('c');
            $nonce = substr(md5(uniqid('nonce_', true)), 0, 16);
            $nonceSixtyFour = base64_encode($nonce);
            $passwordDigest = base64_encode(sha1($nonce . $created . $password, true));
            $token = "UsernameToken Username=\"{$username}\", PasswordDigest=\"{$passwordDigest}\", Nonce=\"{$nonceSixtyFour}\", Created=\"{$created}\"";
            $this->header = array(
                'HTTP_Authorization' => 'WSSE profile="UsernameToken"',
                'HTTP_X-WSSE' => $token,
                'HTTP_ACCEPT' => 'application/json'
            );
            $this->client = static::createClient(array(), $this->header);
        }
    }

    /**
     * Test get user
     *
     * @return none
     */
    public function testGetUserAction_valid_user()
    {
        $this->client->request('GET', '/api/users/usuario1', array(), array(), $this->header);
        $content = $this->client->getResponse()->getContent();
        $usuario = json_decode($content, false);

        // Assert a specific 200 status code
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));

        $this->assertEquals('usuario1', $usuario->username);
        $this->assertEquals('usuario1@bd.com', $usuario->email);
    }

    /**
     * Test get user nonexistent slug
     *
     * @return none
     */
    public function testGetUserAction_invalid_user()
    {
        $this->client->request('GET', '/api/users/user_no_existente');

        // Assert a specific 404 status code
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test get registered users
     *
     * @return none
     */
    public function testGetUserAction_get_registered_users()
    {

        $this->client->request('GET', '/api/users');
        $content = $this->client->getResponse()->getContent();
        $contenidoDecodificado = json_decode($content, false);

        // Assert a specific 200 status code
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertCount(3, $contenidoDecodificado);

        $usuario = $contenidoDecodificado[0];
        $this->assertEquals('admin', $usuario->username);
        $this->assertEquals('admin@qa.int', $usuario->email);

        $usuario = $contenidoDecodificado[1];
        $this->assertEquals('usuario1', $usuario->username);
        $this->assertEquals('usuario1@bd.com', $usuario->email);

        $usuario = $contenidoDecodificado[2];
        $this->assertEquals('usuario2', $usuario->username);
        $this->assertEquals('usuario2@bd.com', $usuario->email);
        //var_dump($contenidoDecodificado);
    }

    /**
     * Test create user
     *
     * @return none
     */
    public function testPostUsersAction()
    {
        //$jsonParam = "username=usuario3&plainPassword=12345&email=" . urlencode("usuario3@bd.com");
        //echo $jsonParam;

        $params = array('username' => 'usuario3',
            'plainPassword' => '12345',
            'email' => 'usuario3@bd.com');
        //$headers = array('CONTENT_TYPE' => 'application/x-www-form-urlencoded');

        $this->client->request('POST', '/api/users', $params, array());
        //, $headers); //, $jsonParam);
        //$content = $this->client->getResponse()->getContent();
        //$contenidoDecodificado = json_decode($content, false);
        //var_dump($contenidoDecodificado);
        // Assert a specific 200 status code
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        //var_dump($this->client->getResponse()->headers);
        $this->assertTrue($this->client->getResponse()->headers->contains('location', 'http://localhost/api/users/usuario3'));

        //limpiar usuario creado
        $user = $this->userManager->findUserByUsername("usuario3");
        $this->assertNotNull($user);
        if ($user) {
            $this->userManager->deleteUser($user);
        }
    }

    /**
     * Test create user invalid data
     *
     * @return none
     */
    public function testPostUsersAction_invalid_data()
    {
        $jsonParam = "username=u3&plainPassword=1&email=" . urlencode("usuario3_bd.com");
        //echo $jsonParam;

        $this->client->request('POST', '/api/users', array(), array(), array('CONTENT_TYPE' => 'application/x-www-form-urlencoded'), $jsonParam);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));

    }

    /**
     * Test delete an existing user
     *
     * @return none
     */
    public function testDeleteUserAction()
    {
        $this->client->request('DELETE', '/api/users/usuario2');

        // Assert a specific 200 status code
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

    /**
     * Test delete user nonexistent
     *
     * @return none
     */
    public function testDeleteUserAction_invalid_user()
    {
        $this->client->request('DELETE', '/api/users/usuario0');

        // Assert a specific 200 status code
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

    /**
     * Test delete user without slug
     *
     * @return none
     * @expectedException Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    public function testDeleteUserAction_without_slug()
    {
        $this->client->request('DELETE', '/api/users');

        // Assert a specific 400 status code
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test update user
     *
     * @return none
     */
    public function testPutUserAction()
    {
        $params = array('plainPassword' => '54321');

        $this->client->request('PUT', '/api/users/usuario2', $params);

        // Assert a specific 200 status code
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

    }

    /**
     * Test update user invalid slug
     *
     * @return none
     */
    public function testPutUserAction_invalid_slug()
    {
        $params = array('plainPassword' => '54321');

        $this->client->request('PUT', '/api/users/usuario4', $params);

        // Assert a specific 200 status code
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test update user invalid password
     *
     * @return none
     */
    public function testPutUserAction_invalid_password()
    {
        $params = array('plainPassword' => '5');
        //$headers = array('CONTENT_TYPE' => 'application/x-www-form-urlencoded');

        $this->client->request('PUT', '/api/users/usuario2', $params);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test update user invalid username
     *
     * @return none
     */
    public function testPutUserAction_invalid_username()
    {
        $params = array('username' => '5');
        //$headers = array('CONTENT_TYPE' => 'application/x-www-form-urlencoded');

        $this->client->request('PUT', '/api/users/usuario2', $params);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test update user invalid email
     *
     * @return none
     */
    public function testPutUserAction_invalid_email()
    {
        $params = array('email' => '5');
        //$headers = array('CONTENT_TYPE' => 'application/x-www-form-urlencoded');

        $this->client->request('PUT', '/api/users/usuario2', $params);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
