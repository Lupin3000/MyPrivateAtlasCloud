# MyPrivate Vagrant Cloud

With MyPrivate Vagrant Cloud, you can quickly and easily manage your Vagrant boxes. MyPrivate Atlas Cloud act similar as HashiCorp Vagrant Cloud.

## Installation and start up

```shell
# git clone
$ git clone https://github.com/Lupin3000/MyPrivateAtlasCloud.git

# change directory
$ cd MyPrivateAtlasCloud

# start via docker-compose
$ docker-compose up -d

# view logs (optional)
$ docker-compose logs -f

# open browser
$ open -a Safari http://localhost:8080/
```

## Destroy

```shell
# shutdown and destroy
$ docker-compose down
```

## Use configuration

To edit application configuration, please use the "_./lib/confg/config.ini_"! For Nginx or PHP configuration, please change the files "_./conf/custom.ini_" (_PHP_) and/or "_./conf/default.conf_" (_Nginx_) before you run docker-compose up command.

```shell
# edit application configuration
$ vim lib/config/config.ini
```

### Change domain

```
[server]
URL = "http://localhost:8080"
```

### Choose different box and json location

Do not forget to change the location path inside "_./conf/default.conf_"!

```
[repository]
box_dir = "/boxes/bin/"
json_dir = "/boxes/meta/"
```

### Enable/Disable Login

```
[login]
; enable/disable login 'on|off'
security = "off"

; set credentials
user = "admin"
password = "test123"
```
