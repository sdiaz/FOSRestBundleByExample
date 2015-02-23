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

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\SecurityExtraBundle\Annotation\Secure;

class UserRoleRestController extends FOSRestController
{

    /**
     * Return all roles for an user identified by username/email.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = true,
     *   description = "Return an user identified by username/email",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param string $slug username or email
     *
     * @return View
     */
    public function getRoleAction($slug)
    {

        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserByUsernameOrEmail($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }

        $view = View::create();
        $view->setData($entity->getRoles())->setStatusCode(200);

        return $view;
    }

    /**
     * Create a Role from the submitted data.<br/>
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new role from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="slug", nullable=false, strict=true, description="Username or Email.")
     * @RequestParam(name="role", nullable=false, strict=true, description="Role.")
     *
     * @return View
     */
    public function postRoleAction(ParamFetcher $paramFetcher)
    {

        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserByUsernameOrEmail($paramFetcher->get('slug'));

        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }

        $entity->addRole($paramFetcher->get('role'));
        $userManager->updateUser($entity);

        $view = View::create();
        $view->setData($entity->getRoles())->setStatusCode(200);

        return $view;
    }

    /**
     * Delete a Role from the submitted data.<br/>
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = true,
     *   description = "Deletes a role from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="slug", nullable=false, strict=true, description="Username or Email.")
     * @RequestParam(name="role", nullable=false, strict=true, description="Role.")
     *
     * @return View
     */
    public function deleteRoleAction(ParamFetcher $paramFetcher)
    {

        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserByUsernameOrEmail($paramFetcher->get('slug'));

        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }

        // We do not check if the Role previously exists.
        $entity->removeRole($paramFetcher->get('role'));
        $userManager->updateUser($entity);

        $view = View::create();
        $view->setData($entity->getRoles())->setStatusCode(200);

        return $view;
    }

}