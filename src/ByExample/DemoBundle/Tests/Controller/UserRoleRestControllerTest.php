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
 * Test class for PortalRest controller
 */
class UserRoleRestControllerTest extends WebTestCase
{

    /**
     * Client
     * @var type
     */
    private $client;

    /**
     * Service Container fos_user.user_manager
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
     * Test get roles registered user
     *
     * @return none
     */
    public function testGetRoleAction_get_roles()
    {
        $this->client->request('GET', '/api/users/admin/roles');
        $content = $this->client->getResponse()->getContent();
        $contenidoDecodificado = json_decode($content, false);

        // Assert a specific 200 status code
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));

        // Assert count 2 permissions for admin
        $this->assertCount(2, $contenidoDecodificado);
    }

    /**
     * Test get roles nonexistant user
     *
     * @return none
     */
    public function testGetRoleAction_nonexistant_user()
    {
        $this->client->request('GET', '/api/users/noexistenteuser/roles');

        // Assert a specific 204 status code
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test creation new role
     *
     * @return none
     */
    public function testPostRolesAction()
    {
        $params = array('rol' => 'ROL_UNO');
        $this->client->request('POST', '/api/users/admin/roles', $params, array());

        // Assert a specific 200 status code
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

    /**
     * Test creation new role invalid data
     *
     * @return none
     */
    public function testPostRolesAction_invalid_data()
    {
        // Assert a specific 400 due to no data
        $params = array();
        $this->client->request('POST', '/api/users/admin/roles', $params, array());
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        // Assert a specific 400 due to malformed data
        $params = array('rolon' => 'ROL_PIMPAMPUM');
        $this->client->request('POST', '/api/users/admin/roles', $params, array());
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        // Assert a specific 400 due to malformed request
        $this->client->request('POST', '/api/users', array(), array(), array('CONTENT_TYPE' => 'application/x-www-form-urlencoded'), $params);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

    /**
     * Test Delete Role registered user
     *
     * @return none
     */
    public function testDeleteRoleAction_delete_rol()
    {
        $this->client->request('DELETE', '/api/users/admin/roles/ROL_UNO');

        // Assert a specific 200 status code
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

    /**
     * Test Delete Role nonexistant role
     *
     * @return none
     */
    public function testDeleteRoleAction_nonexitant()
    {
        $this->client->request('DELETE', '/api/users/admin/roles/ROLE_NOEXISTENTE');

        // Assert a specific 204 status code
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test Delete Roles nonexistant user
     *
     * @return none
     */
    public function testDeleteRoleAction_nonexistan_user()
    {
        $this->client->request('DELETE', '/api/users/noexistenteuser/roles/ROL_DEVELOPER');

        // Assert a specific 204 status code
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }
}
