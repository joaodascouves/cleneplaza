mysql -uclene -pnolepass < sql/install.sql
rm -rf userfiles/*
mkdir -p userfiles/posts/pending
mkdir -p userfiles/users
mkdir -p userfiles/comments
mkdir -p userfiles/news
mkdir -p userfiles/mirrors/fullpage
mkdir -p userfiles/mirrors/preview
chmod -R 777 userfiles/*
