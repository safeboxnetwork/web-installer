![Safebox Logo](./resources/safebox_logo.svg)
# WebInstaller

The WebInstaller is a component of the Safebox project, designed to manage and schedule tasks within a containerized environment. It serves as the installation and configuration web interface for the Safebox platform, enabling for users to deploy and manage containerized applications through an intuitive web-based interface.

## Description

## Description

The WebInstaller provides a comprehensive web-based interface for installing, configuring, and managing the Safebox containerized platform. It simplifies the deployment process through an intuitive step-by-step installation wizard that guides users through:

- **Initial Setup**: Configure authentication credentials, proxy settings, and VPN connectivity
- **Service Management**: Deploy and manage containerized applications from pre-configured templates
- **Application Deployment**: Install popular applications like Nextcloud, Bitwarden, Roundcube, and Guacamole
- **Repository Management**: Add and manage custom GIT repositories for additional application templates
- **System Monitoring**: Track running containers, check for updates, and monitor system services
- **Backup & Storage**: Manage disk resources and backup configurations

The installer operates through multiple interfaces including [install.html](install.html) for initial setup, [manage.html](manage.html) for application management, and communicates with the backend framework-scheduler via Redis or shared directory interfaces. It features both light and dark themes, responsive design, and supports remote access configuration through VPN proxy settings.

Built with PHP, JavaScript, and modern CSS, the WebInstaller handles deployment orchestration through [scan.php](scan.php) and [functions.php](functions.php), while [common.js](common.js) provides client-side functionality for real-time status updates and user interactions.


### Dependencies
The WebInstaller is part of the Safebox platform. For full functionality, it also requires the `safebox/framework-scheduler` image.
You can find the current version of the web-installer image on [Docker Hub](https://hub.docker.com/r/safebox/web-installer). The source code repository is available at [https://github.com/safeboxnetwork/web-installer](https://github.com/safeboxnetwork/web-installer).

## Screenshots
![Framework Scheduler Screenshot](./resources/framework_scheduler_main.png)

## How to Use

### Running the Container

```bash
docker run \
    --rm \
    -v /var/run/docker.sock:/var/run/docker.sock \
    safebox/framework-scheduler:latest
```

### Build Your Own Image
To build the Framework Scheduler image from the source code, follow these steps:
1. Clone the repository:
     ```bash
     git clone https://github.com/safeboxnetwork/web-installer
     cd framework-scheduler
     ```
2. Build the Docker image:
     ```bash
     docker build -t <your-docker-registry>/framework-scheduler:latest .
     ```
3. Run the container:
     ```bash
     docker run --rm -e DOCKER_REGISTRY_URL=<your-docker-registry> -v /var/run/docker.sock:/var/run/docker.sock <your-docker-registry>/framework-scheduler:latest
     ```
     > **Note:** Replace `<your-docker-registry>` with your actual Docker registry URL. Otherwise, the image will use the default Docker registry URL, 'safebox'.

### Environment Variables
The following environment variables can be set to configure the Framework Scheduler:

| Environment Variable | Description |
|--------------------------|-------------|
| `DOCKER_REGISTRY_URL` | Docker registry URL for image operations. Required for pushing to a private registry. |
| `DOCKER_REGISTRY_USERNAME` | Username for Docker registry authentication. |
| `DOCKER_REGISTRY_PASSWORD` | Password for Docker registry authentication. |
| `WEBSERVER_PORT` | Port number for the web interface. Default: `8080`. |

## TODO
The Framework Scheduler is under active development. Future plans include:
- Backup and restore functionality for challenge clients with different users' Safebox platforms.
- Enhanced monitoring and alerting features.
- Enhanced disk space management and alerting features.
- Notifications for better performance and management of your installed applications.
