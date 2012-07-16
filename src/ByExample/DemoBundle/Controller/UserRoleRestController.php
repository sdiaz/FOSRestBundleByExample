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

use BDK\Core\UserBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\Annotations\RequestParam,
    FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Request\ParamFetcher,
    FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View AS FOSView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Validator\ConstraintViolation;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Controller that provides Restful sercies over the resource
 * Users Roles.
 *
 * @NamePrefix("byexample_demo_userrolrest_")
 * @author  Santiago Diaz <santiago.diaz@me.com>
 * @version Release: 0.1
 */
class UserRoleRestController extends Controller
{

    /**
     * Returns all roles
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
     * Create a new role
     *
     * @param string $slug Username or Email
     *
     * @return FOSView
     * @ApiDoc(
     * filters={
     *      {"name"="rol", "dataType"="string"},
     *  }
     * )
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
     * Delete the rol indicated.
     *
     * @param string $slug Username or Email
     * @param string $id   Role name
     *
     * @return FOSView
     * @Secure(roles="ROLE_DEVELOPER")
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