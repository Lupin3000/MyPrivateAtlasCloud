# MyPrivate Vagrant Cloud

With MyPrivate Vagrant Cloud, you can quickly and easily manage your Vagrant boxes. MyPrivate Atlas Cloud act similar as HashiCorp Vagrant Cloud. You can have a look on my [Packer](https://github.com/Lupin3000/Packer) repository for creating Vagrant base boxes by your self.

## Download from GitHub

```shell
# create directory
$ mkdir ~/Projects

# clone repository
$ git clone https://github.com/Lupin3000/MyPrivateAtlasCloud.git ~/Projects/

# change into project directory
$ cd ~/Projects/MyPrivateAtlasCloud/
```

## Build and run application

All build files are located in folder "build". Files of this folder will never come into the environments (_eq. Dev/Test/Prod_).

- docker-compose.yml
- Dockerfile

### Build and run via docker-compose

```shell
# change into build directory
$ cd ~/Projects/MyPrivateAtlasCloud/build/

# start application via docker-compose
$ docker-compose up -d

# view log files from containers (optional)
$ docker-compose logs -f

# open application inside browser
$ open -a Safari http://localhost:8080/

# stop
$ docker-compose stop

# start
$ docker-compose start

# shutdown and destroy
$ docker-compose down
```
## Configuration

### Environment configuration

All environment configuration files are located in folder "environment". Change these files before you  deploy containers inside a cluster/environment.

- Nginx is configured via "_environment/default.conf_"
- PHP is configured via "_environment/custom.ini_"

### Application configuration

All application configuration files are located in folder "lib/config". These files can be changed after after the deployment while running the application.

- App configuration "_lib/config/application.ini_"

**Change domain**

```
[server]
; Domain for JSON files
URL = "http://localhost:8080"
```

**Choose different box and json location**

Do not forget to change the nginx location path!

```
[repository]
; full path to default location
html_path = "/var/www/html/"

; full path to box directory
box_dir = "/var/www/html/boxes/bin/"

; full path to json directory
json_dir = "/var/www/html/boxes/meta/"
```

**Enable/Disable Login**

```
[login]
; enable/disable login 'on|off'
security = "off"

; set credentials
user = "admin"
password = "test123"
```
