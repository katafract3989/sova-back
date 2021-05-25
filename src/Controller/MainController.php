<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Camers;
use App\Entity\Users;
use Symfony\Component\HttpFoundation\Session\Session;


class MainController extends AbstractController
{
    public function index(Connection $connection): Response
    {
       $camers = [];

       foreach($this->getDoctrine()->getRepository(Camers::class)->findAll() as $cam) {
        array_push($camers, $cam->getCam($cam));
        }
        
        return $this->json($camers);
    }
    /**
     * @Route("/login", name="login")
     * 
     */
    public function validation(Connection $connection, Request $request): Response
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(Users::class);
        $user = $userRepo->findOneBy(['phone' => $_POST['phone']]);

        if(!empty($user)) {

            if ($user->getPassword() === md5($_POST['password'])) {

                $session = new Session();
                $session->start();
                $session->set('auth', md5($_POST['phone'] . time()));
                $user->setToken($session->get('auth'));
                $em->flush();

                return $this->json($session->get('auth'));
                
            } else {

            return new Response(
                'Access denied',
                Response::HTTP_FORBIDDEN,
                ['content-type' => 'application/json']
            );
        }
        } else {

            return new Response(
                'No content',
                Response::HTTP_NO_CONTENT,
                ['content-type' => 'application/json']
            );
        }
    }
    /**
     * @Route("/logout", name="logout")
     * 
     */
    public function logout(Connection $connection): Response
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(Users::class);
        $user = $userRepo->findOneBy(['token' => $_POST['token']]);

        if(!empty($user)) {
            $user->setToken('');
        $em->flush();

        return new Response(
            'Session terminated',
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
        } else {

            return new Response(
                'Unable to access the session',
                Response::HTTP_UNAUTHORIZED,
                ['content-type' => 'application/json']
            ); 
        }
        
        
    }
}
