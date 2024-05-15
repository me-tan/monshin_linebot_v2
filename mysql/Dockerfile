# use mysql-server for Apple Silicon Mac
FROM mysql/mysql-server:5.6

# timezone environment
ENV TZ=Asia/Tokyo

# initialize database
# 初期化用のSQLファイルをマウントがある場合は、下記のコメントを外してファイルを配置する．
COPY initdb.d/* /docker-entrypoint-initdb.d/

CMD [ "mysqld" ]