pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'bagasfathoni/foodhive-app'
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
                    // Membangun image Docker berdasarkan Dockerfile
                    sh "docker build -t ${DOCKER_IMAGE}:${env.BUILD_ID} ."
                }
            }
        }

        stage('Run Container') {
            steps {
                script {
                    // Berhentikan container lama jika ada, lalu jalankan yang baru
                    sh "docker stop ${DOCKER_IMAGE} || true"
                    sh "docker rm ${DOCKER_IMAGE} || true"
                    sh "docker run -d --name ${DOCKER_IMAGE} -p 8081:80 ${DOCKER_IMAGE}:${env.BUILD_ID}"
                }
            }
        }
    }
}
