[![CD](https://github.com/mqwerty/github-action-k8s-deploy/workflows/CD/badge.svg?branch=master)](https://github.com/mqwerty/github-action-k8s-deploy/actions?query=workflow%3ACD)
[![Docker Image Size (latest)](https://img.shields.io/docker/image-size/edmitry/github-action-k8s-deploy/latest)](https://hub.docker.com/repository/docker/edmitry/github-action-k8s-deploy)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=mqwerty_github-action-k8s-deploy&metric=alert_status)](https://sonarcloud.io/dashboard?id=mqwerty_github-action-k8s-deploy)

# GitHub Actions + DockerHub + DigitalOcean + Kubernetes

## Подготовка и развертывание вручную

Требуется установить [kubectl](https://kubernetes.io/docs/tasks/tools/install-kubectl/) и [doctl](https://github.com/digitalocean/doctl).  

Создаем кластер Kubernetes в DigitalOcean.  
Кластер с именем `kube`, состоит из `1` виртуалки размера `S` в регионе `fra1`.
```bash
doctl kube cluster create kube --set-current-context --region fra1 --node-pool "name=kube-test;size=s-2vcpu-2gb;count=1"
```

Собираем докер образ и пушим его в DockerHub:
```bash
docker build . --file Dockerfile --tag edmitry/github-action-k8s-deploy
docker tag edmitry/github-action-k8s-deploy edmitry/github-action-k8s-deploy:latest
docker push edmitry/github-action-k8s-deploy:latest
```

Разворачиваем приложение:
```bash
kubectl apply -f kube.yml
```

Проверяем что все развернулось:
```bash
kubectl get deployments
kubectl get pod
doctl compute load-balancer list
curl {load-balancer-ip}
# {"result":"test"}
```

Проверяем версию докер образа:
```bash
kubectl get pod
kubectl describe pod {name}
# Image ID: docker-pullable://edmitry/github-action-k8s-deploy@sha256:5d0ba35bdd128eea01fbfdee60b0d4f91ccd97a43ff303f3d8cf99b1ce9638c8
```

## CI/CD

При обновлении ветки master в репозитории запускается GitHub Action Runner, который собирает тестовый Docker образ приложения и
запускает в нем тесты. Также тесты запускаются для всех пул-реквестов, без выполнения дальнейших шагов.

Если тесты прошли успешно, то для мастер ветки запускается сборка Docker образа и публикация его в DockerHub.

Далее дергается Kubernetes, чтобы запустить деплой приложения.
Запущено 3 реплики, по одной заменяем на новую версию.
При запуске новых реплик используются проверки readinessProbe.
Если проверки не проходят, то остаются работать старые версии.

## Очистка

Удаляем приложение:
```bash
kubectl delete -f kube.yml
```

Возвращаем обратно контекст kubectl:
```bash
kubectl config set-context docker-desktop
```
