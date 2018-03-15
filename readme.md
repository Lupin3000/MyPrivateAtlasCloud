# MyPrivate Vagrant Cloud

With MyPrivate Vagrant Cloud, you can quickly and easily manage your Vagrant boxes. MyPrivate Atlas Cloud act similar as HashiCorp Vagrant Cloud. You can have a look on my [Packer](https://github.com/Lupin3000/Packer) repository for creating Vagrant base boxes by your self.

## Installation and start up

```shell
# clone repository
$ git clone https://github.com/Lupin3000/MyPrivateAtlasCloud.git

# change in application root directory
$ cd MyPrivateAtlasCloud

# start application via docker-compose
$ docker-compose up -d

# view log files from containers (optional)
$ docker-compose logs -f

# open application inside browser
$ open -a Safari http://localhost:8080/
```

## Destroy environment

```shell
# shutdown and destroy
$ docker-compose down
```

## Configuration

The HTTP port can be changed via "docker-compose.yml". If needed, you can edit the configuration for Nginx (_./src/default.conf_) and/or PHP (_./src/custom.ini_). Please change these files before you run docker-compose up command.

To edit application configuration, please use the "./lib/config/application.ini"! 

**Change domain**

```
[server]
URL = "http://localhost:8080"
```

**Choose different box and json location**

Do not forget to change the location path inside "_./src/default.conf_"!

```
[repository]
box_dir = "/boxes/bin/"
json_dir = "/boxes/meta/"
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
