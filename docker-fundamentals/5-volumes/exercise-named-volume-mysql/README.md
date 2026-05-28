# Named volume for MySQL (exercise + solution)

| Lesson | Use |
| ------ | --- |
| [Exercise: named volume for MySQL](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-exercise-named-volume-for-mysql) | Run the commands below with `docker run` (no Compose) |
| [Solution: named volume for MySQL](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-solution-named-volume-for-mysql) | [`demo.sh`](demo.sh) |
| [MySQL with a named volume (Compose)](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-mysql-with-a-named-volume) | [bind-vs-named-php](../bind-vs-named-php/) (`compose.mysql.yaml`) |

Lesson 5.4 uses Compose; this exercise uses plain `docker volume` + `docker run` so persistence is visible without a compose file.

## Exercise

```bash
docker volume create mysql_data
docker volume ls | grep mysql_data

docker run -d \
  --name my_mysql \
  -e MYSQL_ROOT_PASSWORD=root \
  -v mysql_data:/var/lib/mysql \
  mysql:8.0

docker ps --filter name=my_mysql

# Wait until MySQL accepts connections, then seed:
docker exec my_mysql mysql -uroot -proot -e \
  "CREATE DATABASE IF NOT EXISTS exercise_db; \
   USE exercise_db; \
   CREATE TABLE IF NOT EXISTS notes (id INT AUTO_INCREMENT PRIMARY KEY, body TEXT); \
   INSERT INTO notes (body) VALUES ('persist me'); \
   SELECT * FROM notes;"

docker stop my_mysql
docker rm my_mysql

docker run -d \
  --name my_mysql \
  -e MYSQL_ROOT_PASSWORD=root \
  -v mysql_data:/var/lib/mysql \
  mysql:8.0

docker exec my_mysql mysql -uroot -proot exercise_db -e "SELECT * FROM notes;"
```

## Solution

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

## Checklist

- [ ] Volume `mysql_data` exists before the first `docker run`
- [ ] Row `persist me` survives `docker rm` when the same volume is remounted
- [ ] You can explain why the volume outlived the container

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`demo.sh`](demo.sh) | Full walkthrough with assertions |
| [`clean.sh`](clean.sh) | `docker rm -f my_mysql` and `docker volume rm mysql_data` |
| [`.env.example`](.env.example) | Optional overrides for names/passwords |

## Clean up

```bash
./clean.sh
```

`docker volume rm` is the data-loss step — same idea as `docker compose down -v` in lesson 5.4.

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `my_mysql` name in use | `./clean.sh` |
| MySQL not ready | Wait a few seconds; `demo.sh` polls automatically |
| Port 3306 conflict | This demo does not publish 3306; only `docker exec` is used |

← [Chapter 5 — Volumes](../README.md)
