# MyPrivate Vagrant Cloud

## Installation and start up

```shell
# git clone
$ git clone https://github.com/Lupin3000/MyPrivateAtlasCloud.git

# change directory
$ cd MyPrivateAtlasCloud

# start via docker-compose
$ docker-compose up -d

# view logs (optional)
$ docker-compose logs

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

### Enable/Disable Login

```
[login]
; enable/disable login 'on|off'
security = "off"
user = "admin"
password = "test123"
```
