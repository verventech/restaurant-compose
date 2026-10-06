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

        stage('Create Environment File') {
            steps {
                sh '''
                    cat << 'EOF' > .env
PGHOST=restaurant-db
PGDATABASE=restaurant_db
PGUSER=postgres
PGPASSWORD=mysecretpassword
PGPORT=5432
HOST_PORT=8081
EOF
                '''
            }
        }

        stage('Deploy to Staging') {
            steps {
                sshagent([SSH_CREDS_ID]) {
                    // 1. Remove remote db directory to ensure clean folder creation
                    sh "ssh -o StrictHostKeyChecking=no ${VM_USER}@${STAGING_IP} 'rm -rf ~/db'"
                    
                    // 2. Transfer fresh compose, .env, and db/ directory
                    sh "scp -r -o StrictHostKeyChecking=no docker-compose.yml .env db ${VM_USER}@${STAGING_IP}:~/"
                    
                    sh """
                        ssh -o StrictHostKeyChecking=no ${VM_USER}@${STAGING_IP} '
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
                // Corrected port from 8080 to match HOST_PORT 8081
                input message: "Verify Staging environment at http://${STAGING_IP}:8081. Promote to Production?", ok: "Deploy!"
            }
        }

        stage('Deploy to Production') {
            steps {
                sshagent([SSH_CREDS_ID]) {
                    // Added -r flag for recursive copy of db/ directory
                    sh "scp -r -o StrictHostKeyChecking=no docker-compose.yml .env db ${VM_USER}@${PROD_IP}:~/"
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