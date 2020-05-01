<?php

    namespace App\Models;

    use MF\Model\Model;

    class Usuario extends Model {

        private $id;
        private $nome;
        private $email;
        private $senha;

        public function __get($atributo){
            return $this->$atributo;
        }

        public function __set($atributo, $valor){
            $this->$atributo = $valor;
        }

        public function salvar(){

            $query = '
                insert into usuarios (nome, email, senha) values (:nome, :email, :senha)
            ';

            $stmt = $this->db->prepare($query);
            
            $stmt->bindValue(':nome', $this->__get('nome'));
            $stmt->bindValue(':email', $this->__get('email'));
            $stmt->bindValue(':senha', $this->__get('senha'));
            $stmt->execute();

            return $this;
        }

        //validar se as informações são validas
        public function validarCadastro(){

            $valido = TRUE;

            if (strlen($this->__get('nome')) < 3 ){
                $valido = FALSE;
            }

            if (strlen($this->__get('email')) < 6 || 
                                                    !strpos($this->__get('email'),'@') || 
                                                    !strpos($this->__get('email'), '.') ){

                $valido = FALSE;
            }

            if (strlen($this->__get('senha')) < 6 ){
                $valido = FALSE;
            }

            return $valido;
        }

        public function getUsuarioPorEmail(){
            $query = 'select nome, email from usuarios where email = :email';

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':email', $this->__get('email'));
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        }

        public function autenticar(){
            $query = 'select id, nome, email from usuarios where email = :email and senha = :senha';

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':email', $this->__get('email'));
            $stmt->bindValue(':senha', $this->__get('senha'));
            $stmt->execute();

            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($usuario['id'] != '' && $usuario['nome'] != ''){
                $this->__set('id',$usuario['id']);
                $this->__set('nome',$usuario['nome']);
            }

        }

        public function getAll(string $termo){
            
            $query = 'select 
                            u.id, 
                            u.nome, 
                            u.email,
                            (
                                select
                                    count(*)
                                from
                                    usuarios_seguidores as us 
                                where 
                                    us.id_usuario = :id and
                                    us.id_usuario_seguindo = u.id
                            ) as seguindo_sn 
                      from 
                            usuarios  u
                      where 
                            (u.nome like :termo or
                            u.email like :termo) and
                            u.id != :id';
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':termo', '%'.$termo.'%');
            $stmt->bindValue(':id', $this->__get('id'));
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        public function seguirUsuario(int $id_usuario_seguindo){
            $query = 'insert into 
                            usuarios_seguidores(id_usuario, id_usuario_seguindo) 
                      values 
                            (:id_usuario, :id_usuario_seguindo)';
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id_usuario', $this->__get('id'));
            $stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
            $stmt->execute();
        }

        public function deixarDeSeguir(int $id_usuario_seguindo){
            $query = 'delete from 
                            usuarios_seguidores
                      where 
                            id_usuario = :id_usuario and
                            id_usuario_seguindo = :id_usuario_seguindo';
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id_usuario', $this->__get('id'));
            $stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
            $stmt->execute();
        }

        //Informação sobre Usuário
        public function getInfoUsuario(){

            $query = 'select nome from 
                            usuarios
                      where 
                            id = :id_usuario' ;

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id_usuario', $this->__get('id'));
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        //Total de Tweets
        public function getTotalTweets(){

            $query = 'select count(*) as total_tweet from 
                            tweets
                      where 
                            id_usuario = :id_usuario' ;

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id_usuario', $this->__get('id'));
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        //Total de usuarios que estamos seguindo
        public function getTotalSeguindo(){

            $query = 'select count(*) as total_seguindo from 
                            usuarios_seguidores
                      where 
                            id_usuario = :id_usuario' ;

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id_usuario', $this->__get('id'));
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        //Total de usuários que estão nos seguindo
        public function getTotalSeguidores(){

            $query = 'select count(*) as total_seguidores from 
                            usuarios_seguidores
                      where 
                            id_usuario_seguindo = :id_usuario' ;

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id_usuario', $this->__get('id'));
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

    }



?>