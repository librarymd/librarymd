## Run sphinx localy

1. Install sphinx 2.2.x
2. Edit sphinx.conf
3. Start brew install sphinx --with-mysql
4. Edit sphinx_sample.conf with your credentials.
5. searchd --config sphinx_sample.conf
6. indexer --config sphinx_sample.conf torrents_search --rotate