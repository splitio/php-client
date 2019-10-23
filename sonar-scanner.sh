#/bin/bash -e

if [ "$TRAVIS_BRANCH" == 'develop' ]; then
  TARGET_BRANCH='master'
else
  TARGET_BRANCH='develop'
fi

sonar_scanner() {
  local params="$@"

  vendor/bin/sonar-scanner \
    -Dsonar.host.url='https://sonarqube.split-internal.com' \
    -Dsonar.login=$SONAR_TOKEN \
    -Dsonar.ws.timeout='300' \
    -Dsonar.projectName='php-client' \
    -Dsonar.exclusions="**/tests/**/*.*" \
    -Dsonar.links.ci='https://travis-ci.com/splitio/php-client' \
    -Dsonar.links.scm='https://github.com/splitio/php-client' \
    -Dsonar.pullrequest.provider='GitHub' \
    -Dsonar.pullrequest.github.repository='splitio/php-client'
    "${params}"

  return $?
}

if [ "$TRAVIS_PULL_REQUEST" != "false" ]; then
  sonar_scanner -Dsonar.pullrequest.key=$TRAVIS_PULL_REQUEST -Dsonar.pullrequest.branch=$TRAVIS_PULL_REQUEST_BRANCH -Dsonar.pullrequest.base=$TRAVIS_BRANCH
else
  sonar_scanner -Dsonar.branch.name=$TRAVIS_BRANCH -Dsonar.branch.target=$TARGET_BRANCH
fi
