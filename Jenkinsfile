pipeline {
    agent any
    
    environment {
        IMAGE_NAME = 'abelchris/foodhive-app' 
        REGISTRY_CREDENTIALS = 'dockerhub-credentials'
        DOCKER_REGISTRY = 'https://registry.hub.docker.com'
    }
    
    stages {
        stage('Verify Environment') {
            steps {
                script {
                    echo "=== Checking Docker Installation ==="
                    bat "docker --version"
                    bat "docker info"
                    
                    echo "=== Checking Existing Images ==="
                    bat "docker images"
                }
            }
        }
        
        stage('Checkout') { 
            steps { 
                checkout scm 
            } 
        }
        
        stage('Build Docker Image') { 
            steps { 
                script {
                    echo "=== Building Docker Image ==="
                    bat "docker build -t ${IMAGE_NAME}:${BUILD_NUMBER} ."
                    
                    echo "=== Verifying Built Image ==="
                    bat "docker images ${IMAGE_NAME}"
                }
            } 
        }
        
        stage('Test Image') {
            steps {
                script {
                    echo "=== Testing Docker Image ==="
                    // Run container briefly to test
                    bat """
                        docker run --rm -d --name test-${BUILD_NUMBER} ${IMAGE_NAME}:${BUILD_NUMBER}
                        timeout /t 5 /nobreak
                        docker stop test-${BUILD_NUMBER}
                    """
                }
            }
        }
        
        stage('Login to Docker Hub') {
            steps {
                script {
                    withCredentials([usernamePassword(
                        credentialsId: REGISTRY_CREDENTIALS,
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        echo "=== Logging in to Docker Hub as ${DOCKER_USER} ==="
                        bat "echo %DOCKER_PASS% | docker login -u %DOCKER_USER% --password-stdin"
                    }
                }
            }
        }
        
        stage('Push Docker Image') {
            steps {
                script {
                    echo "=== Pushing ${IMAGE_NAME}:${BUILD_NUMBER} ==="
                    retry(3) {
                        bat "docker push ${IMAGE_NAME}:${BUILD_NUMBER}"
                    }
                    
                    echo "=== Tagging as latest ==="
                    bat "docker tag ${IMAGE_NAME}:${BUILD_NUMBER} ${IMAGE_NAME}:latest"
                    
                    echo "=== Pushing ${IMAGE_NAME}:latest ==="
                    retry(3) {
                        bat "docker push ${IMAGE_NAME}:latest"
                    }
                }
            }
        }
    }
    
    post {
        always {
            script {
                echo "=== Logging out from Docker Hub ==="
                bat "docker logout"
            }
        }
        success {
            echo "✅ SUCCESS: Image ${IMAGE_NAME}:${BUILD_NUMBER} pushed to Docker Hub"
            echo "View at: https://hub.docker.com/r/${IMAGE_NAME}"
        }
        failure {
            echo "❌ FAILED: Check console output for errors"
            // Cleanup images on failure (opsional)
            script {
                bat "docker rmi ${IMAGE_NAME}:${BUILD_NUMBER} || exit 0"
            }
        }
    }
}
