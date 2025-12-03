FROM php:8.2-apache

# Copia todos os arquivos pro diretório padrão do Apache
COPY . /var/www/html

# Expõe a porta do servidor
EXPOSE 80
