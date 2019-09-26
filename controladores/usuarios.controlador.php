<?php

class ControladorUsuarios{

  /*=============================================
  INGRESO DE USUARIO
  =============================================*/

  static public function ctrIngresoUsuario(){

    if(isset($_POST["ingUsuario"])){

      if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"]) &&
       preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingPassword"])){

        $encriptar = crypt($_POST["ingPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

        $tabla = "usuarios";

        $item = "usuario";
        $valor = $_POST["ingUsuario"];

        $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

        if($respuesta["usuario"] == $_POST["ingUsuario"] && $respuesta["password"] == $encriptar){

          if($respuesta["estado"] == 1){

            $_SESSION["iniciarSesion"]="ok";
            $_SESSION["id"] = $respuesta["id"];
            $_SESSION["nombre"] = $respuesta["nombre"];
            $_SESSION["usuario"] = $respuesta["usuario"];
            $_SESSION["foto"] = $respuesta["foto"];
            $_SESSION["perfil"] = $respuesta["perfil"];

            /*=============================================
            REGISTRAR FECHA PARA SABER EL ÚLTIMO LOGIN
            =============================================*/

            date_default_timezone_set('America/Mexico_City');

            $fecha = date('Y-m-d');
            $hora = date('H:i:s');

            $fechaActual = $fecha.' '.$hora;

            $item1 = "ultimo_login";
            $valor1 = $fechaActual;

            $item2 = "id";
            $valor2 = $respuesta["id"];

            $ultimoLogin = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

            if($ultimoLogin == "ok"){

              echo '<script>

                window.location = "inicio";

              </script>';

            }

          }else{

            echo '<br>
              <div class="alert alert-danger">El usuario aún no está activado</div>';

          }

        }else{

          echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';

        }

      }else{

        echo'<script>

          swal({
            type: "error",
            title: "¡El Usuario no puede ir con los campos vacíos o llevar caracteres especiales!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
            }).then(function(result){
            if (result.value) {
              window.location = "inicio";
            }
          })

        </script>';
      }

    }

  }

  /*=============================================
  REGISTRO DE USUARIO
  =============================================*/

  static public function ctrCrearUsuario(){

    if(isset($_POST["nuevoUsuario"]) ){

      if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
         preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
         preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"]) && $_POST["nuevoPerfil"]!=""){

        $tabla = "usuarios";
        $item = "usuario";
        $valor = $_POST["nuevoUsuario"];

        $duplicado = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);

        if(!$duplicado){

          /*=============================================
          VALIDAR IMAGEN
          =============================================*/

          $ruta = "vistas/img/usuarios/default/anonymous.png";

          if(isset($_FILES["nuevaFoto"]["tmp_name"])){

            list($ancho, $alto) = getimagesize($_FILES["nuevaFoto"]["tmp_name"]);

            $nuevoAncho = 500;
            $nuevoAlto = 500;

            /*=============================================
            CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA FOTO DEL USUARIO
            =============================================*/

            $directorio = "vistas/img/usuarios/".$_POST["nuevoUsuario"];

            mkdir($directorio, 0755);

            /*=============================================
            DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
            =============================================*/

            if($_FILES["nuevaFoto"]["type"] == "image/jpeg"){

              /*=============================================
              GUARDAMOS LA IMAGEN EN EL DIRECTORIO
              =============================================*/

              $aleatorio = mt_rand(100,999);

              $ruta = "vistas/img/usuarios/".$_POST["nuevoUsuario"]."/".$aleatorio.".jpg";

              $origen = imagecreatefromjpeg($_FILES["nuevaFoto"]["tmp_name"]);

              $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

              imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

              imagejpeg($destino, $ruta);

            }

            if($_FILES["nuevaFoto"]["type"] == "image/png"){

              /*=============================================
              GUARDAMOS LA IMAGEN EN EL DIRECTORIO
              =============================================*/

              $aleatorio = mt_rand(100,999);

              $ruta = "vistas/img/usuarios/".$_POST["nuevoUsuario"]."/".$aleatorio.".png";

              $origen = imagecreatefrompng($_FILES["nuevaFoto"]["tmp_name"]);

              $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

              imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

              imagepng($destino, $ruta);

            }

          }

          // var_dump($_POST["nuevoNombre"]);
          // var_dump($_POST["nuevoUsuario"]);
          // var_dump($_POST["nuevoPerfil"]);
          // var_dump($_POST["nuevoPassword"]);
          
          $estado = 0;
          $encriptar = crypt($_POST["nuevoPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

          $datos = array("nombre" => $_POST["nuevoNombre"],
                         "usuario" => $_POST["nuevoUsuario"],
                         "password" => $encriptar,
                         "perfil" => $_POST["nuevoPerfil"],
                         "foto"=>$ruta,
                         "estado"=>$estado);

          $respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);

          if($respuesta == "ok"){

            echo '<script>

              swal({
                type: "success",
                title: "¡El Usuario ha sido guardado correctamente!",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
                }).then(function(result){
                if(result.value){
                  window.location = "usuarios";
                }
              });

            </script>';

          }else{

            echo '<script>

              swal({
                type: "error",
                title: "No se pudo ingresar en la base de datos",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
                }).then(function(result){
                if(result.value){
                  window.location = "usuarios";
                }
              });

            </script>';

          }

        }else{

          echo'<script>

            swal({
              title: "Error de Datos",
              text: "¡Este Usuario ya existe en la base de datos!",
              type: "error",
              confirmButtonText: "¡Cerrar!"
            });

            $("#nuevoUsuario").val("");

          </script>';

        }  

      }else{

        echo '<script>

          swal({
            type: "error",
            title: "¡Los campos no pueden ir vacíos o llevar caracteres especiales!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
            }).then(function(result){
            if(result.value){
              window.location = "usuarios";
            }
          });

        </script>';

      }

    }

  }

  /*=============================================
  MOSTRAR USUARIO
  =============================================*/

  static public function ctrMostrarUsuarios($item, $valor){

    $tabla = "usuarios";

    $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

    return $respuesta;
  }

  /*=============================================
  EDITAR USUARIO
  =============================================*/

  static public function ctrEditarUsuario(){

    if(isset($_POST["editarUsuario"])){

      if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"])){

        /*=============================================
        VALIDAR IMAGEN
        =============================================*/

        $ruta = $_POST["fotoActual"];

        if(isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])){

          list($ancho, $alto) = getimagesize($_FILES["editarFoto"]["tmp_name"]);

          $nuevoAncho = 500;
          $nuevoAlto = 500;

          /*=============================================
          CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA FOTO DEL USUARIO
          =============================================*/

          $directorio = "vistas/img/usuarios/".$_POST["editarUsuario"];

          /*=============================================
          PRIMERO PREGUNTAMOS SI EXISTE OTRA IMAGEN EN LA BD
          =============================================*/

          if(!empty($_POST["fotoActual"]) && $_POST["fotoActual"]!="vistas/img/usuarios/default/anonymous.png"){

            unlink($_POST["fotoActual"]);

          }

          /*=============================================
          DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
          =============================================*/

          if($_FILES["editarFoto"]["type"] == "image/jpeg"){

            /*=============================================
            GUARDAMOS LA IMAGEN EN EL DIRECTORIO
            =============================================*/

            $aleatorio = mt_rand(100,999);

            $ruta = "vistas/img/usuarios/".$_POST["editarUsuario"]."/".$aleatorio.".jpg";

            $origen = imagecreatefromjpeg($_FILES["editarFoto"]["tmp_name"]);

            $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

            imagejpeg($destino, $ruta);

          }

          if($_FILES["editarFoto"]["type"] == "image/png"){

            /*=============================================
            GUARDAMOS LA IMAGEN EN EL DIRECTORIO
            =============================================*/

            $aleatorio = mt_rand(100,999);

            $ruta = "vistas/img/usuarios/".$_POST["editarUsuario"]."/".$aleatorio.".png";

            $origen = imagecreatefrompng($_FILES["editarFoto"]["tmp_name"]);

            $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

            imagepng($destino, $ruta);

          }

        }

        $tabla = "usuarios";

        if($_POST["editarPassword"] != ""){

          if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])){

            $encriptar = crypt($_POST["editarPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

          }else{

            echo'<script>

              swal({                  
                type: "error",
                title: "¡La contraseña no puede ir vacía o llevar caracteres especiales!",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
                }).then(function(result){
                if (result.value) {
                  window.location = "usuarios";
                }
              })

            </script>';

          }

        }else{

          $encriptar = $_POST["passwordActual"];

        }

        $datos = array("nombre" => $_POST["editarNombre"],
                       "usuario" => $_POST["editarUsuario"],
                       "password" => $encriptar,
                       "perfil" => $_POST["editarPerfil"],
                       "foto" => $ruta);

        $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

        if($respuesta == "ok"){

          echo'<script>

            swal({
              type: "success",
              title: "El Usuario ha sido editado correctamente",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
              }).then(function(result){
              if (result.value) {
                window.location = "usuarios";
              }
            })

          </script>';

        }

      }else{

        echo'<script>

          swal({
            type: "error",
            title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
            }).then(function(result){
            if (result.value) {
              window.location = "usuarios";
            }
          })

        </script>';

      }

    }

  }

  /*=============================================
  BORRAR USUARIO
  =============================================*/

  static public function ctrBorrarUsuario(){

    if(isset($_GET["idUsuario"])){

      $tabla ="usuarios";
      $datos = $_GET["idUsuario"];

      if($_GET["fotoUsuario"] != "vistas/img/usuarios/default/anonymous.png"){

        unlink($_GET["fotoUsuario"]);
        rmdir('vistas/img/usuarios/'.$_GET["usuario"]);

      }

      $respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);

      if($respuesta == "ok"){

        echo'<script>

          swal({
            type: "success",
            title: "El Usuario ha sido borrado correctamente",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
            }).then(function(result){
            if (result.value) {
              window.location = "usuarios";
            }
          })

        </script>';

      }else{
        
        echo'<script>

          swal({
            type: "error",
            title: "Error al borrar el Usuario, verifique que no esté asociado a una Transacción o a un Aviso",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
            }).then(function(result){
              if (result.value) {
                window.location = "usuarios";
              }
          })

        </script>';
      }

    }

  }

}