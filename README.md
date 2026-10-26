# Medicaladmin

Panel administrativo (PHP + MySQL) para gestionar las clínicas clientes del sistema HMS:
membresías, usuarios de cada clínica y el personal del panel.

Interfaz basada en la plantilla [ElaAdmin](https://colorlib.com/polygon/elaadmin/index.html) de Colorlib (licencia MIT).

## Módulos

| Página | Rol | Función |
|---|---|---|
| `index.php` | admin, superadmin | Dashboard: clínicas activas, membresías vigentes, por vencer y vencidas |
| `membresias.php` | superadmin | Clínicas clientes: NIT, plan, fechas de activación y vencimiento |
| `usuarios_clinicas.php` | admin, superadmin | Usuarios de cada clínica con su rol |
| `usuarios.php` | superadmin | Personal del panel |

## Estructura

```
ajax/        Endpoints JSON (listar / guardar / obtener / eliminar)
class/       Conexion.php (PDO, lee config.php)
includes/    header, menu, footer, csrf.php, validacion.php
assets/js/   utils.js (CSRF, escape, DataTables) + un JS por página
sql/         Scripts de cambios en la base de datos
```

## Instalación (local o servidor)

1. Copiar `config.example.php` como `config.php` y completar los datos de la base de datos.
   `config.php` está en `.gitignore`: cada entorno tiene el suyo y nunca se sube al repositorio.
   En el servidor usar `'debug' => false`.
2. Ejecutar en la base de datos los scripts de `sql/` que aún no se hayan aplicado.
3. En el servidor, bloquear `.git` y `config.php` con `.htaccess`.
4. Correo (recuperación de contraseña y avisos): en `config.php` definir `url_base`,
   `correo_modo` (`mail` en el servidor, `archivo` en local → se guardan en `logs/correos/`)
   y `correo_remitente` (una cuenta del mismo dominio).
5. Avisos de vencimiento: en cPanel → Cron Jobs, una vez al día:
   `php -q /home/USUARIO/public_html/HMSadmin/cron/avisos_vencimiento.php >> /home/USUARIO/public_html/HMSadmin/logs/avisos.log 2>&1`

## Flujo de trabajo

- Se trabaja en la rama `developer`; `main` es producción.
- Cuando los cambios están probados: fusionar `developer` en `main`, hacer push y en cPanel
  → Git Version Control → **Update from Remote**.

## Seguridad

- Los endpoints AJAX exigen sesión, rol y token CSRF (cabecera `X-CSRF-Token`, la envía `utils.js`).
- El login limita los intentos fallidos (tabla `login_intentos_admin`) y regenera el ID de sesión.
- Los datos se validan en el servidor (`includes/validacion.php`) y se escapan al mostrarse.
