# Kubernetes Quick Start

Infra is not fully IaC yet; setup uses several manual steps.

Cluster setup:
```
kubectl create namespace aspirepress
helm repo add bitnami https://charts.bitnami.com/bitnami
helm install -n aspirepress redis bitnami/redis --set architecture=standalone
helm install -n aspirepress postgresql bitnami/postgresql

cp secrets.sample.yaml secrets.yaml
vi secrets.yaml # ... edit according to instructions ...
kubectl apply -n aspirepress -f secrets.yaml  

kubectl apply -n aspirepress -f deployment.yaml
```
