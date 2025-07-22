FROM gitpod/workspace-full

# Install Apache, PHP, MySQL, and required PHP extensions
RUN sudo apt-get update && sudo apt-get install -y \
    apache2 php php-mysql php-mbstring unzip \
    mysql-server curl
