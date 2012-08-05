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

namespace ByExample\DemoBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\View\View AS FOSView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Controller that provides Restful services over the resource Users Roles using user_manager container.
 *
 * @NamePrefix("byexample_demo_userrolrest_")
 * @author Santiago Diaz <santiago.diaz@me.com>
 */
class UserRoleRestController extends Controller
{

    /**
     * Returns all roles for an user by username/email.
     *
     * @param string $slug Username or Email
     *
     * @return FOSView
     * @Secure(roles="ROLE_USER")
     * @ApiDoc()
     */
    public function getRolesAction($slug)
    {
        $view = FOSView::create();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($slug);

        if ($user) {
            $view->setStatusCode(200)->setData($user->getRoles());
        } else {
            $view->setStatusCode(204);
        }

        return $view;
    }

    /**
     * Create a new role for an user by username/email.
     *
     * @param string $slug Username or Email
     *
     * @return FOSView
     * @Secure(roles="ROLE_USER")
     * @ApiDoc()
     */
    public function postRolesAction($slug)
    {
        $view = FOSView::create();
        $request = $this->getRequest();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($slug);

        if ($user && $request->get('rol')) {
            $user->addRole($request->get('rol'));
            $userManager->updateUser($user);
            $view->setStatusCode(200)->setData($user->getRoles());
        } else {
            $view->setStatusCode(400);
        }

        return $view;
    }

    /**
     * Delete the role indicated for an user by username/email.
     *
     * @param string $slug Username or Email
     * @param string $id   Role name
     *
     * @return FOSView
     * @Secure(roles="ROLE_USER")
     * @ApiDoc()
     */
    public function deleteRoleAction($slug, $id)
    {
        $view = FOSView::create();
        $request = $this->getRequest();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($slug);

        if ($user && $request->get('id') && $user->hasRole($request->get('id'))) {
            $user->removeRole($request->get('id'));
            $userManager->updateUser($user);
            $view->setStatusCode(200)->setData('User role removed.');
        } else {
            $view->setStatusCode(204)->setData("No data available");
        }
        return $view;
    }

}