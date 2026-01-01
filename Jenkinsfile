pipeline {
    agent any
    environment {
        IMAGE_NAME = 'bagasfathoni/foodhive-app' 
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
                        // Login ke Docker Hub
                        bat "echo %DOCKER_PASS% | docker login -u %DOCKER_USER% --password-stdin"
                        
                        // Push versi spesifik (angka build)
                        bat "docker push ${IMAGE_NAME}:${BUILD_NUMBER}"
                        
                        // Tag ke latest dan push lagi
                        bat "docker tag ${IMAGE_NAME}:${BUILD_NUMBER} ${IMAGE_NAME}:latest"
                        bat "docker push ${IMAGE_NAME}:latest"
                    }
                }
            }
        }
    }
    post {
        always {
            // Cleanup: logout dari Docker
            bat "docker logout"
        }
        success {
            echo "Pipeline berhasil! Image ${IMAGE_NAME}:${BUILD_NUMBER} telah di-push ke Docker Hub"
        }
        failure {
            echo "Pipeline gagal! Cek logs untuk detail error"
        }
    }
}
