<?php
require_once 'includes/funcoes.php';

$raiz = '';
$tituloPagina = 'O instituto';
$paginaAtual = 'sobre';
require 'includes/header.php';
?>

<section class="cabecalho-secao">
    <div>
        <h1>O instituto</h1>
        <p class="sub">Como uma escola clandestina virou a maior universidade livre da rede.</p>
    </div>
</section>

<section class="bloco texto">
    <h2>História</h2>
    <p>Em 2077, depois do Grande Apagão, cinco engenheiros expulsos da corporação Arasaka-Pinhais se esconderam
       num antigo shopping abandonado do setor 9. Ali começaram a ensinar jovens da periferia a reprogramar os
       implantes baratos que as corporações vendiam. A turma clandestina cresceu, ganhou servidores próprios e,
       em 2081, foi registrada como NEON-77, em homenagem ao letreiro de neon quebrado que ainda pisca na entrada.</p>
    <p>Hoje o instituto ocupa a torre Araucária inteira e forma mais de três mil netrunners por ciclo.</p>
</section>

<section class="bloco missao">
    <div>
        <h2>Missão</h2>
        <p>Formar netrunners capazes de usar a tecnologia para proteger as pessoas, não para controlá-las.</p>
    </div>
    <div>
        <h2>Visão</h2>
        <p>Ser, até 2110, a rede de ensino independente mais respeitada do hemisfério sul.</p>
    </div>
    <div>
        <h2>Valores</h2>
        <p>Código aberto, ética hacker, privacidade como direito e nenhum implante sem consentimento.</p>
    </div>
</section>

<section class="bloco">
    <h2>Mentores</h2>
    <div class="mentores">
        <article>
            <h3>Dra. Yuki "Ghost" Tanaka</h3>
            <p>Hacking Neural Ético. Invadiu os servidores de três corporações e nunca foi rastreada.</p>
        </article>
        <article>
            <h3>Prof. Marcus Kade</h3>
            <p>Engenharia de Implantes. Metade do corpo é cibernética, e ele mesmo projetou cada peça.</p>
        </article>
        <article>
            <h3>ORÁCULO-9</h3>
            <p>Design de Hologramas. A primeira IA docente do país, com doutorado defendido em 0,4 segundo.</p>
        </article>
        <article>
            <h3>Capitã Rosa Valente</h3>
            <p>Pilotagem de Drones. Ex-piloto de entregas que venceu o campeonato clandestino de 2094.</p>
        </article>
    </div>
</section>

<section class="bloco">
    <h2>Estrutura e unidades</h2>
    <table class="tabela">
        <thead><tr><th>Unidade</th><th>Local</th><th>O que tem lá</th></tr></thead>
        <tbody>
            <tr><td>Torre Araucária</td><td>Setor 9, Neo-Curitiba</td><td>Sede, laboratórios de imersão e a biblioteca de dados.</td></tr>
            <tr><td>Hangar Pinhais</td><td>Zona aeroportuária</td><td>Pista de testes de drones e oficina de implantes.</td></tr>
            <tr><td>Nó Subterrâneo</td><td>Túneis do antigo metrô</td><td>Servidores próprios, resfriados com água da chuva ácida filtrada.</td></tr>
        </tbody>
    </table>
</section>

<section class="bloco">
    <h2>Curiosidades</h2>
    <ul class="diferenciais">
        <li>O letreiro de neon da entrada está quebrado desde 2077 e é proibido consertá-lo.</li>
        <li>A prova final de Hacking Neural é invadir o próprio sistema de notas do instituto. Quem consegue, passa.</li>
        <li>O café da cantina é sintético, mas a ORÁCULO-9 garante que tem 98% de sabor de café de verdade.</li>
    </ul>
</section>

<?php require 'includes/footer.php'; ?>
