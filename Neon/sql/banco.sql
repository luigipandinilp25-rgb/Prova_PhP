-- =========================================================
-- NEON-77 — Instituto Cibernético de Neo-Curitiba
-- Script de criação do banco de dados com dados de exemplo
-- =========================================================

-- Garante que os acentos sejam importados corretamente, inclusive pelo terminal
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS neon77;
CREATE DATABASE neon77 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE neon77;

-- Protocolos = cursos oferecidos pelo instituto
CREATE TABLE protocolos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    duracao_ciclos INT NOT NULL,
    mentor VARCHAR(100) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Netrunners = alunos matriculados
CREATE TABLE netrunners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    codinome VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    idade INT NOT NULL,
    nivel_implante TINYINT NOT NULL,
    protocolo_id INT NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_netrunner_protocolo FOREIGN KEY (protocolo_id)
        REFERENCES protocolos(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO protocolos (nome, descricao, duracao_ciclos, mentor) VALUES
('Hacking Neural Ético', 'Invasão e defesa de sistemas conectados diretamente ao cérebro, sempre dentro da ética hacker.', 8, 'Dra. Yuki "Ghost" Tanaka'),
('Engenharia de Implantes Cibernéticos', 'Projeto, fabricação e manutenção de implantes, do braço mecânico ao olho de visão noturna.', 10, 'Prof. Marcus Kade'),
('Design de Hologramas', 'Criação de interfaces holográficas, anúncios em 3D e camuflagem visual.', 6, 'ORÁCULO-9'),
('Pilotagem de Drones', 'Controle de drones de entrega, reconhecimento e corrida pelos céus da megacidade.', 4, 'Capitã Rosa Valente'),
('Direito Corporativo Distópico', 'Como ler contratos de 900 páginas e defender cidadãos contra as megacorporações.', 8, 'Dr. Augusto Ferraz-9');

INSERT INTO netrunners (nome, codinome, email, idade, nivel_implante, protocolo_id, criado_em) VALUES
('Ana Kowalski', 'Glitch', 'glitch@neon77.net', 22, 3, 1, '2099-01-10 09:00:00'),
('Bruno Takeda', 'Zero', 'zero@neon77.net', 19, 2, 1, '2099-01-12 10:30:00'),
('Carla Mendes', 'Voltagem', 'voltagem@neon77.net', 25, 5, 2, '2099-01-15 14:00:00'),
('Diego Ramos', 'Ferrugem', 'ferrugem@neon77.net', 31, 4, 2, '2099-01-20 08:45:00'),
('Elisa Nakamura', 'Holo', 'holo@neon77.net', 20, 1, 3, '2099-02-01 11:10:00'),
('Felipe Souza', 'Rotor', 'rotor@neon77.net', 18, 2, 4, '2099-02-03 16:20:00'),
('Giovana Prado', 'Cláusula', 'clausula@neon77.net', 27, 1, 5, '2099-02-07 09:15:00'),
('Heitor Lima', 'Bitrate', 'bitrate@neon77.net', 23, 3, 1, '2099-02-11 13:00:00'),
('Íris Valença', 'Prisma', 'prisma@neon77.net', 21, 2, 3, '2099-02-14 15:40:00'),
('João Ferreira', 'Sucata', 'sucata@neon77.net', 35, 5, 2, '2099-02-20 10:00:00'),
('Karina Okafor', 'Nimbus', 'nimbus@neon77.net', 24, 3, 4, '2099-03-01 08:30:00'),
('Lucas Andrade', 'Firewall', 'firewall@neon77.net', 29, 4, 1, '2099-03-05 17:00:00'),
('Marina Costa', 'Pixel', 'pixel@neon77.net', 17, 1, 3, '2099-03-09 12:25:00'),
('Nicolas Yamada', 'Overclock', 'overclock@neon77.net', 26, 5, 2, '2099-03-15 09:50:00'),
('Olívia Barros', 'Liminar', 'liminar@neon77.net', 30, 2, 5, '2099-03-21 14:35:00'),
('Pedro Saito', 'Hélice', 'helice@neon77.net', 20, 3, 4, '2099-03-28 11:05:00');
