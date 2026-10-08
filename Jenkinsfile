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
        APP_NAME = "restaurant-app"
    }

    stages {
        stage('Build & Push') {
            steps {
                script {
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDS_ID, passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                        sh "echo \$PASS | docker login -u \$USER --password-stdin"
                        
                        // Build & Push single image artifact for both environments
                        sh "docker build -t ${DOCKER_USER}/${APP_NAME}:v${TAG} ."
                        sh "docker push ${DOCKER_USER}/${APP_NAME}:v${TAG}"
                    }
                }
            }
        }

        stage('Create Environment File') {
            steps {
                withCredentials([string(credentialsId: 'restaurant-app-PGPASS', variable: 'DB_PASS')]) {
                    sh """
                        cat << EOF > .env
PGHOST=restaurant-db
PGDATABASE=restaurant_db
PGUSER=postgres
PGPASSWORD=${DB_PASS}
PGPORT=5432
HOST_PORT=8081
EOF
                    """
                }
            }
        }

        stage('Deploy to Staging') {
            steps {
                sshagent([SSH_CREDS_ID]) {
                    // 1. create directory in staging in jenkins/home/restaurant-app
                    sh "ssh -o StrictHostKeyChecking=no ${VM_USER}@${STAGING_IP} 'mkdir -p ~/${APP_NAME}'"
                    
                    // 2. Transfer fresh compose, .env, and db/ to jenkins/home/restaurant-app directory
                    sh "scp -r -o StrictHostKeyChecking=no docker-compose.yml .env db ${VM_USER}@${STAGING_IP}:~/${APP_NAME}/"
                    
                    // 3. again ssh to actually run compose
                    sh """
                        ssh -o StrictHostKeyChecking=no ${VM_USER}@${STAGING_IP} '
                           cd ~/${APP_NAME}
                            
                            export TAG=${TAG}
                            export DOCKER_USER=${DOCKER_USER}

                            docker compose -p ${APP_NAME} pull
                            docker compose -p ${APP_NAME} up -d --force-recreate --remove-orphans
                        '
                    """
                }
            }
        }

        stage('Approval Gate') {
            steps {
                input message: "Verify Staging environment at http://${STAGING_IP}:8081. Promote to Production?", ok: "Deploy!"
            }
        }

        stage('Deploy to Production') {
            steps {
                sshagent([SSH_CREDS_ID]) {
                    sh "ssh -o StrictHostKeyChecking=no ${VM_USER}@${PROD_IP} 'mkdir -p ~/${APP_NAME}'"
                    sh "scp -r -o StrictHostKeyChecking=no docker-compose.yml .env db ${VM_USER}@${PROD_IP}:~/${APP_NAME}/"
                    sh """
                        ssh -o StrictHostKeyChecking=no ${VM_USER}@${PROD_IP} '
                            cd ~/${APP_NAME}
                            
                            export TAG=${TAG}
                            export DOCKER_USER=${DOCKER_USER}

                            docker compose -p ${APP_NAME} pull
                            docker compose -p ${APP_NAME} up -d --remove-orphans --force-recreate
                        '
                    """
                }
            }
        }
    }
}