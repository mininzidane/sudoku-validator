## Install steps
```bash
docker-compose up -d
docker-compose exec php composer i
```

## Run from console (correct and incorrect files located in public/csv or add your own csv files)
```bash
docker-compose exec php bin/console sudoku:check-csv-file initial-sudoku.csv
docker-compose exec php bin/console sudoku:check-csv-file initial-sudoku-incorrect.csv
```

## Run from API
```bash
curl -F csvFile=@initial-sudoku.csv http://localhost:8077/validate-sudoku-csv-file
```

## Run tests
```bash
docker-compose exec php bin/phpunit tests/
```
