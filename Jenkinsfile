pipeline {
    agent any
    
    environment {
        // GANTI INI dengan nama project Anda yang sebenarnya
        DOCKER_USERNAME = 'bagasfathoni'
        APP_NAME = 'foodhive-app'  
        
        IMAGE_NAME = "${DOCKER_USERNAME}/${APP_NAME}"
        REGISTRY_CREDENTIALS = 'dockerhub-credentials'
    }
    
    stages {
        stage('Checkout') { 
            steps { 
                checkout scm 
            } 
        }
        
        stage('Build Docker Image') { 
            steps { 
                script {
                    echo "Building ${IMAGE_NAME}:${BUILD_NUMBER}"
                    bat "docker build -t ${IMAGE_NAME}:${BUILD_NUMBER} ."
                }
            } 
        }
        
        stage('Push Docker Image') {
            steps {
                script {
                    withCredentials([usernamePassword(
                        credentialsId: REGISTRY_CREDENTIALS, 
                        usernameVariable: 'DOCKER_USER', 
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        // Login
                        bat "echo %DOCKER_PASS% | docker login -u %DOCKER_USER% --password-stdin"
                        
                        // Push dengan build number
                        bat "docker push ${IMAGE_NAME}:${BUILD_NUMBER}"
                        
                        // Tag dan push latest
                        bat "docker tag ${IMAGE_NAME}:${BUILD_NUMBER} ${IMAGE_NAME}:latest"
                        bat "docker push ${IMAGE_NAME}:latest"
                    }
                }
            }
        }
    }
    
    post {
        always {
            bat "docker logout"
        }
        success {
            echo "✅ Image berhasil di-push:"
            echo "   ${IMAGE_NAME}:${BUILD_NUMBER}"
            echo "   ${IMAGE_NAME}:latest"
        }
    }
}
