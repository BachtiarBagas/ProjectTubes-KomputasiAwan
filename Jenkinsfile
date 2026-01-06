pipeline {
    agent any
    
    environment {
        DOCKER_IMAGE = 'food-ordering-app'
        DOCKER_TAG = "${BUILD_NUMBER}"
        AZURE_REGISTRY = 'your-registry.azurecr.io'
        AZURE_CREDENTIALS = credentials('azure-credentials')
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
                echo 'Code checked out successfully'
            }
        }
        
        stage('Build Docker Image') {
            steps {
                script {
                    echo 'Building Docker image...'
                    sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} ."
                    sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest"
                }
            }
        }
        
        stage('Test') {
            steps {
                script {
                    echo 'Running tests...'
                    sh """
                        docker run --rm ${DOCKER_IMAGE}:${DOCKER_TAG} php -l /var/www/html/index.php
                        docker run --rm ${DOCKER_IMAGE}:${DOCKER_TAG} php -l /var/www/html/config.php
                        docker run --rm ${DOCKER_IMAGE}:${DOCKER_TAG} php -l /var/www/html/dashboard.php
                    """
                }
            }
        }
        
        stage('Push to Azure Container Registry') {
            steps {
                script {
                    echo 'Pushing to Azure Container Registry...'
                    sh """
                        echo ${AZURE_CREDENTIALS_PSW} | docker login ${AZURE_REGISTRY} -u ${AZURE_CREDENTIALS_USR} --password-stdin
                        docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${AZURE_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}
                        docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${AZURE_REGISTRY}/${DOCKER_IMAGE}:latest
                        docker push ${AZURE_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}
                        docker push ${AZURE_REGISTRY}/${DOCKER_IMAGE}:latest
                    """
                }
            }
        }
        
        stage('Deploy to Azure') {
            steps {
                script {
                    echo 'Deploying to Azure Web App...'
                    sh """
                        az webapp config container set \
                            --name your-webapp-name \
                            --resource-group your-resource-group \
                            --docker-custom-image-name ${AZURE_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG} \
                            --docker-registry-server-url https://${AZURE_REGISTRY} \
                            --docker-registry-server-user ${AZURE_CREDENTIALS_USR} \
                            --docker-registry-server-password ${AZURE_CREDENTIALS_PSW}
                        
                        az webapp restart --name your-webapp-name --resource-group your-resource-group
                    """
                }
            }
        }
    }
    
    post {
        success {
            echo 'Pipeline completed successfully!'
        }
        failure {
            echo 'Pipeline failed!'
        }
        always {
            sh 'docker system prune -f'
        }
    }
}
