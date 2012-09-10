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
 * Test class for default controller
 */
class SecurityControllerTest extends WebTestCase
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
     * Test login, get token.
     *
     * @return none
     */
    public function testPostLoginAction()
    {
        $client = static::createClient();
        $params = array('_username' => 'admin', '_password' => 'admin');
        $header = array(
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_Content-Type' => 'application/x-www-form-urlencoded',
        );

        $client->request('POST', '/security/tokens/creates', $params, array(), $header);
        $content = $client->getResponse()->getContent();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test login, invalid user.
     *
     * @return none
     */
    public function testPostLoginAction_invalid_user()
    {
        $client = static::createClient();
        $params = array('_username' => 'nonexistent', '_password' => 'admin');
        $header = array(
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_Content-Type' => 'application/x-www-form-urlencoded',
        );

        $client->request('POST', '/security/tokens/creates', $params, array(), $header);
        $content = $client->getResponse()->getContent();
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
    }

    /**
     * Test logout
     *
     * @return none
     */
    public function testLogout()
    {
        $this->client->request('GET', '/security/tokens/destroys');
        $content = $this->client->getResponse()->getContent();
        $contenidoDecodificado = json_decode($content, false);

        // Assert a specific 200 status code
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

}
