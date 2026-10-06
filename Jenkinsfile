pipeline {
    agent any
    environment {
        DOCKER_USER = "verventech"
        VM_USER = "verjenkins"
        STAGING_IP = "192.168.1.99"
        PROD_IP = "192.168.1.100" 
        DOCKER_CREDS_ID = "docker-hub-pat"
        SSH_CREDS_ID = "vm-ssh-key"
        TAG = "${env.BUILD_NUMBER}"
    }

    stages {
        stage('Build & Push') {
            steps {
                script {
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDS_ID, passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                        sh "echo \$PASS | docker login -u \$USER --password-stdin"
                        
                        // Build & Push single image artifact for both environments
                        sh "docker build -t ${DOCKER_USER}/restaurant-app:v${TAG} ."
                        sh "docker push ${DOCKER_USER}/restaurant-app:v${TAG}"
                    }
                }
            }
        }

        stage('Deploy to Staging') {
            steps {
                sshagent([SSH_CREDS_ID]) {
                    sh "scp -o StrictHostKeyChecking=no docker-compose.yml ${VM_USER}@${STAGING_IP}:~/docker-compose.yml"
                    sh """
                        ssh -o StrictHostKeyChecking=no ${VM_USER}@${STAGING_IP} '
                           # Stop and remove existing conflicting containers if present
                            docker rm -f restaurant-db restaurant-web restaurant-app 2>/dev/null || true
                           export TAG=${TAG}
                            export DOCKER_USER=${DOCKER_USER}
                            docker compose pull
                            docker compose up -d --remove-orphans
                        '
                    """
                }
            }
        }

        stage('Approval Gate') {
            steps {
                input message: "Verify Staging environment at http://${STAGING_IP}:8080. Promote to Production?", ok: "Deploy!"
            }
        }

        stage('Deploy to Production') {
            steps {
                sshagent([SSH_CREDS_ID]) {
                    sh "scp -o StrictHostKeyChecking=no docker-compose.yml ${VM_USER}@${PROD_IP}:~/docker-compose.yml"
                    sh """
                        ssh -o StrictHostKeyChecking=no ${VM_USER}@${PROD_IP} '
                            # Stop and remove existing conflicting containers if present
                            docker rm -f restaurant-db restaurant-web restaurant-app 2>/dev/null || true
                            export TAG=${TAG}
                            export DOCKER_USER=${DOCKER_USER}
                            docker compose pull
                            docker compose up -d --remove-orphans
                        '
                    """
                }
            }
        }
    }
}