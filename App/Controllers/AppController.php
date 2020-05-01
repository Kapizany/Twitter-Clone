<?php

	namespace App\Controllers;

	//os recursos do miniframework
	use MF\Controller\Action;
	use MF\Model\Container;

	class AppController extends Action {

		public function timeline(){
			
			$this->validaAutenticacao();

			//recuperar tweets
			$tweet = Container::getModel('Tweet');
			$tweet->__set('id_usuario', $_SESSION['id']); //id do usuario da sessão
			$tweets = $tweet->getAll();

			$this->view->tweets = $tweets;

			$usuario = Container::getModel('Usuario');
			$usuario->__set('id',$_SESSION['id']);
			$this->view->info_usuario = $usuario->getInfoUsuario();
			$this->view->total_tweets = $usuario->getTotalTweets();
			$this->view->total_seguindo = $usuario->getTotalSeguindo();
			$this->view->total_seguidores = $usuario->getTotalSeguidores();
			
			$this->render('timeline');

		}

		public function tweet(){

			$this->validaAutenticacao();

		 	$tweet = Container::getModel('Tweet');
		 	$tweet->__set('tweet', $_POST['tweet']);
		 	$tweet->__set('id_usuario', $_SESSION['id']);

		 	$tweet->salvar();
		 	header('Location: /timeline');

		}

		public function removerTweet(){

			$this->validaAutenticacao();

			$id_tweet = isset($_POST['id_tweet'])?$_POST['id_tweet']:'';

			if ($id_tweet != ''){
				
				$tweet = Container::getModel('Tweet');
		 		$tweet->__set('id', $id_tweet);
		 		$tweet->__set('id_usuario', $_SESSION['id']);

		 		$tweet->deletar();
			}

			header('Location: /timeline');
		}

		public function validaAutenticacao(){

			session_start();
			$user['id'] = isset($_SESSION['id'])?$_SESSION['id']: '';
			$user['nome'] = isset($_SESSION['nome'])?$_SESSION['nome']: '';

			if ($user['id'] == '' || $user['nome'] == ''){
				header('Location: /?login=erro');
			} 
		}

		public function quemSeguir(){
			$this->validaAutenticacao();

			$pesquisarPor = isset($_GET['pesquisarPor']) ? $_GET['pesquisarPor'] : '';
			$usuarios = Array();

			if ($pesquisarPor != ''){
				$usuario = Container::getModel('Usuario');
				$usuario->__set('id',$_SESSION['id']);
				$usuarios = $usuario->getAll($pesquisarPor);
			}

			$this->view->usuarios = $usuarios;

			$usuario = Container::getModel('Usuario');
			$usuario->__set('id',$_SESSION['id']);
			$this->view->info_usuario = $usuario->getInfoUsuario();
			$this->view->total_tweets = $usuario->getTotalTweets();
			$this->view->total_seguindo = $usuario->getTotalSeguindo();
			$this->view->total_seguidores = $usuario->getTotalSeguidores();

			$this->render('quemSeguir');
		}

		public function acao(){
			$this->validaAutenticacao();

			//qual ação e qual id do usuario que sera seguido

			$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
			$id_usuario_seguindo = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';

			$usuario = Container::getModel('Usuario');
			$usuario->__set('id',$_SESSION['id']);

			if ($acao == 'seguir'){
				$usuario->seguirUsuario($id_usuario_seguindo);
			} else if ($acao == 'deixar_de_seguir'){
				$usuario->deixarDeSeguir($id_usuario_seguindo);
			}

			header('Location: /quem_seguir');
		}
	}

?>