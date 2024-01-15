#!/bin/sh
LIB_DIRECTORY=${PWD}/../lib/BSC
BUNDLE_DIRECTORY=${PWD}/sfBundle
SWAGGER_DIRECTORY=${PWD}/.swagger-codegen
SWAGGER_IGNORE=${PWD}/.swagger-codegen-ignore

SWAGGER_CODEGEN_CONFIG='{
  "packageName": "BSCApi",
  "variableNamingConvention": "camelCase",
  "packagePath": "sfBundle",
  "invokerPackage": "BSC",
  "modelPackage": "Model",
  "apiPackage": "Service"
}'

echo "$SWAGGER_CODEGEN_CONFIG" > "${PWD}/swagger_config.json"

docker run --rm -v "${PWD}:/local" swaggerapi/swagger-codegen-cli generate -i /local/openapi.yml -l php-symfony -o /local -c /local/swagger_config.json

if [ -d "$BUNDLE_DIRECTORY" ]; then

  MODEL_DIRECTORY=${LIB_DIRECTORY}/Model

  if [ -d "$MODEL_DIRECTORY" ]; then
    echo "rm $MODEL_DIRECTORY"
    rm -rf "${MODEL_DIRECTORY}"
  fi

  echo "mv Model to $MODEL_DIRECTORY"
  mv "${BUNDLE_DIRECTORY}/Model" "${MODEL_DIRECTORY}"

  echo "mv routing.yml to root"
  mv "${BUNDLE_DIRECTORY}/Resources/config/routing.yml" "${PWD}/routing.yml"

  echo "replace swagger_server to bsc"
  sed -i.bak 's|swagger_server|bsc|g' "${PWD}/routing.yml"

  echo "rm $BUNDLE_DIRECTORY"
  rm -rf "${BUNDLE_DIRECTORY}"
  rm "${PWD}/routing.yml.bak"
  rm "${PWD}/swagger_config.json"

fi

echo "rm $SWAGGER_DIRECTORY"
echo "rm $SWAGGER_IGNORE"
rm -rf "${SWAGGER_DIRECTORY}"
rm "${SWAGGER_IGNORE}"
