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

```shell
# edit application configuration
$ vim lib/config.ini
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
