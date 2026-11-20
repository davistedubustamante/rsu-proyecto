# RSU — inicio rápido en GitHub Codespaces

Esta guía corresponde a la rama `exam/integracion` del fork de laboratorio. No modifica el repositorio original.

## 1. Iniciar los contenedores

Abre una terminal en Codespaces y ejecuta **desde la raíz del repositorio**:

```bash
sh docker/crear-env.sh
docker compose config --quiet
docker compose up -d --build
docker compose ps
curl -fsS http://localhost:8080/up
```

- El primer comando genera `.env` con credenciales aleatorias si aún no existe. **Guarda la contraseña de administrador en un lugar seguro** y no la compartas en capturas ni en GitHub.
- Espera a que los contenedores estén saludables; el primer build puede demorar.
- Si `curl` falla, revisa `docker compose logs --tail=100 app web db`.
- No ejecutes `docker compose down -v`: borra los volúmenes que contienen la base de datos y los archivos persistentes.

## 2. Abrir el panel en el navegador

En VS Code web abre **Ports** y busca el puerto **8080**. Su visibilidad inicial es **Private**.

Abre la URL reenviada y verifica primero el inicio de sesión. Para conectar teléfonos Android externos, cambiar la visibilidad de **solo ese puerto** a **Public** (si GitHub lo permite en tu entorno). No publiques el puerto de MySQL (3306), ni phpMyAdmin (8081), ni otros puertos.

**Advertencia:** pasar el puerto 8080 a público expone el panel y la API a Internet. Antes de compartir la URL, confirma contraseñas robustas, que no haya datos personales, y que el entorno sea solo de laboratorio. No incluyas la contraseña en chats/capturas.

## 3. URL HTTPS y Laravel

Codespaces asigna una URL con forma `https://<nombre-del-codespace>-8080.app.github.dev`; utiliza **la URL exacta** indicada en Ports, no una URL inventada. Para sesiones y redirecciones, puede ser necesario configurar `APP_URL` con esa URL real en `.env` y `SESSION_SECURE_COOKIE=true` cuando el proxy HTTPS esté correctamente reconocido.

El Nginx del proyecto reenvía `X-Forwarded-Proto` según su conexión local HTTP. Si observas errores de sesión, inicio de sesión, redirecciones a HTTP o contenido mixto, revisa **trusted proxies**, cabeceras HTTPS y CSRF en Laravel antes de exponer el servicio. No desactives CSRF ni la verificación TLS como atajo.

Después de ajustar `.env`, ejecuta `docker compose up -d --force-recreate app web` y vuelve a comprobar `/up` y el flujo de login.

## 4. Flutter Conductor y Ciudadano

Las dos aplicaciones están en repositorios distintos. Compila sus APK mediante sus workflows de GitHub Actions, con `API_URL` apuntando a la URL HTTPS verificada del puerto 8080. También existe configuración manual del servidor en el inicio de sesión de las aplicaciones.

No recompiles APK hasta comprobar que la API responde desde Internet y que el puerto es realmente público.

## 5. Limitación crítica

**Codespaces no garantiza 48 horas de disponibilidad ininterrumpida**. Se suspende por inactividad y tiene una duración máxima de sesión configurable por GitHub; la URL dejará de responder cuando el Codespace esté detenido. No utilizar mecanismos de actividad artificial para eludir límites.

Conserva una versión estable, realiza respaldos autorizados y verifica el estado del Codespace antes y durante el examen. `docker compose down` sin `-v` conserva normalmente los volúmenes, pero **la eliminación del Codespace puede eliminar sus volúmenes**. Exporta respaldos fuera del Codespace si contienen datos que deban conservarse.

## 6. Diagnóstico

```bash
docker compose ps
docker compose logs --tail=100 app web db
docker compose config --quiet
curl -i http://localhost:8080/up
```

Si la compilación falla por falta de memoria con una máquina pequeña, revisa primero logs y recursos, y considera aumentar la máquina únicamente si la cuota gratuita lo permite.
