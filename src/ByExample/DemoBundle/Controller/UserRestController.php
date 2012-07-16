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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;                  // @ApiDoc(resource=true, description="Filter",filters={{"name"="a-filter", "dataType"="string", "pattern"="(foo|bar) ASC|DESC"}})
use FOS\RestBundle\Controller\Annotations\QueryParam,       // Parameters in GET data @QueryParam(name="name", requirements="\d+", default="1", description="Page of the overview.")
    FOS\RestBundle\Controller\Annotations\RequestParam,     // Parameters in POST data @RequestParam(name="firstname", requirements="[a-z]+", description="Firstname.") Strict -> returns 400
    FOS\RestBundle\Controller\Annotations\Prefix,           // Prefix Route annotation class @Prefix("/api")
    FOS\RestBundle\Controller\Annotations\NamePrefix,       // NamePrefix Route annotation class @NamePrefix("bdk_core_user_userrest_")
    FOS\RestBundle\Controller\Annotations\View,             // If used, the template variable name used to render templating formats can be configured (default 'data'):
    FOS\RestBundle\Request\ParamFetcher,                    // Helper to validate parameters of the active request
    FOS\RestBundle\View\RouteRedirectView,                  // Route based redirect implementation
    FOS\RestBundle\View\View AS FOSView;                    // Default View implementation.
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Validator\ConstraintViolation;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Controller that provides Restful sercies over the resource
 * Users.
 *
 * @NamePrefix("byexample_demo_userrest_")
 * @author  Santiago Diaz <santiago.diaz@me.com>
 * @version Release: 0.1
 */
class UserRestController extends Controller
{

    /**
     * Returns user list.
     *
     * @return FOSView
     * @Secure(roles="ROLE_USER")
     * @ApiDoc()
     */
    public function getUsersAction()
    {
        $view = FOSView::create();
        $userManager = $this->container->get('fos_user.user_manager');
        $data = $userManager->findUsers();
        if ($data) {
            $view->setStatusCode(200)->setData($data);
        }
        return $view;
    }

    /**
     * Returns the user espcified by username or email.
     *
     * @param string $slug Username or Email
     *
     * @return FOSView
     * @Secure(roles="ROLE_USER")
     * @ApiDoc()
     */
    public function getUserAction($slug)
    {
        $view = FOSView::create();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($slug);

        if ($user) {
            $view->setStatusCode(200)->setData($user);
        } else {
            $view->setStatusCode(204);
        }

        return $view;
    }

    /**
     * Creates a new user.
     *
     * @return FOSView
     * @Secure(roles="ROLE_USER")
     * @ApiDoc(
     * filters={
     *      {"name"="username", "dataType"="string"},
     *      {"name"="email", "dataType"="string"},
     *      {"name"="plainPassword", "dataType"="string"},
     *      {"name"="role", "dataType"="string"}
     *  }
     * )
     */
    public function postUsersAction()
    {
        $request = $this->getRequest();
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->createUser();

        //var_dump($request->request);

        $user->setUsername($request->get('username'));
        $user->setEmail($request->get('email'));
        $user->setPlainPassword($request->get('plainPassword'));
        $user->addRole($request->get('role'));

        $validator = $this->get('validator');
        //UTILIZAR GRUPO DE VALIDACION 'Registration' DEL FOSUserBund
        $errors = $validator->validate($user, array('Registration'));
        if (count($errors) == 0) {
            $userManager->updateUser($user);
            $param = array("slug" => $user->getUsername());
            $view = RouteRedirectView::create("byexample_demo_userrest_get_user", $param);
        } else {
            $view = $this->obtener_errors_view($errors);
        }
        return $view;
    }

    /**
     * Update an user especified by username or email.
     *
     * @param string $slug Username or Email
     *
     * @return FOSView
     * @Secure(roles="IS_AUTHENTICATED_ANONYMOUSLY")
     * @ApiDoc(
     * filters={
     *      {"name"="username", "dataType"="string"},
     *      {"name"="email", "dataType"="string"},
     *      {"name"="plainPassword", "dataType"="string"}
     *  }
     * )
     */
    public function putUserAction($slug)
    {
        $request = $this->getRequest();
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->findUserByUsernameOrEmail($slug);
        if (!$user) {
            $view = FOSView::create();
            $view->setStatusCode(204);
            return $view;
        }

        if ($request->get('username')) {
            $user->setUsername($request->get('username'));
        }
        if ($request->get('email')) {
            $user->setEmail($request->get('email'));
        }
        if ($request->get('plainPassword')) {
            $user->setPlainPassword($request->get('plainPassword'));
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($user, array('Registration'));

        if (count($errors) == 0) {
            $userManager->updateUser($user);
            $view = FOSView::create();
            $view->setStatusCode(204);
        } else {
            $view = $this->obtener_errors_view($errors);
        }
        return $view;
    }

    /**
     * Delete the user specified by username or email.
     *
     * @param string $slug Username or Email
     *
     * @return FOSView
     * @Secure(roles="ROLE_DEVELOPER")
     * @ApiDoc()
     */
    public function deleteUserAction($slug)
    {
        $view = FOSView::create();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($slug);
        if ($user) {
            $userManager->deleteUser($user);
            $view->setStatusCode(204)->setData("User removed.");
        } else {
            $view->setStatusCode(204)->setData("No data available.");
        }
        return $view;
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return FOSView
     *
     */
    private function obtener_errors_view($errors)
    {
        $msgs = array();
        $it = $errors->getIterator();
        //$val = new \Symfony\Component\Validator\ConstraintViolation();
        foreach ($it as $val) {
            $msg = $val->getMessage();
            $params = $val->getMessageParameters();
            //usando dominio de traduccion 'validators' del FOSUserBundle
            $msgs[$val->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = FOSView::create($msgs);
        $view->setStatusCode(400);
        return $view;
    }

}