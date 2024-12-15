# Kubernetes Quick Start

Infra is not fully IaC yet; there are some manual steps to set up:

Cluster setup and prerequisites:
```
kubectl create namespace aspirepress
helm repo add bitnami https://charts.bitnami.com/bitnami
helm install -n aspirepress redis bitnami/redis --set architecture=standalone
helm install -n aspirepress postgresql bitnami/postgresql
kubectl create secret -n aspirepress generic ac-secrets --from-literal="redis-password=$(kubectl get secret --namespace aspirepress redis -o jsonpath='{.data.redis-password}' | base64 -d)" --from-literal="db-password=$(kubectl get secret --namespace aspirepress postgresql -o jsonpath='{.data.postgres-password}' | base64 -d)" 
```

To install AspireCloud to the cluster:
```
kubectl apply -n aspirepress -f webapp.yaml
```
