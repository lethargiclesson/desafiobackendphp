# Big Bang - Desafio Backend
micro-serviço que receba requisições HTTP no formato REST que receba como parâmetro o nome de uma cidade ou uma combinação de latitude e longitude e retorne uma sugestão de playlist (array com o título das músicas) de acordo com a temperatura atual da cidade.

## Regras de negócio
* Se a temperatura (Celsius) estiver acima de 30 graus, sugerir músicas para festa
* Se a temperatura está entre 15 e 30 graus, sugerir músicas do gênero Pop.
* Entre 10 e 14 graus, sugerir músicas do gênero Rock
* Abaixo de 10 graus, segerir músicas clássicas.
