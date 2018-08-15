properties([
    buildDiscarder(logRotator(artifactDaysToKeepStr: "", artifactNumToKeepStr: "", daysToKeepStr: "", numToKeepStr: "5"))
])

pipeline {
    agent {
        dockerfile {
            filename "tests/Dockerfile"
        }
    }

    stages {
        stage("Tests") {
            parallel {
                stage("Unit") {
                    steps {
                        sh "make tests"
                    }
                }
            }
        }

        stage("Quality") {
            parallel {
                stage("Code styling") {
                    steps {
                        sh "make cs"
                    }
                }

                stage("Static analysis") {
                    steps {
                        sh "make stan"
                    }
                }
            }
        }
    }
}
