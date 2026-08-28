-- ============================================================
--  NextStep — Banco de Dados
--  AV2 TCC — Ensino Médio Técnico em Informática
--  Alunos: Vinicius e Ingridy | UNASP Campinas
--  Normalizado até a 3ª Forma Normal (3FN)
-- ============================================================

CREATE DATABASE IF NOT EXISTS nextstep
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE nextstep;

-- ============================================================
-- TABELA: perfis
-- Tipos de usuário — separado para cumprir 3FN
-- (evita repetir a string "admin"/"usuario" em cada linha)
-- ============================================================
CREATE TABLE perfis (
  id_perfil INT         NOT NULL AUTO_INCREMENT,
  nome      VARCHAR(30) NOT NULL,
  CONSTRAINT pk_perfis   PRIMARY KEY (id_perfil),
  CONSTRAINT uq_perfis   UNIQUE (nome)
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: usuarios
-- Dados de acesso e perfil do usuário
-- FK para perfis — sem repetição (3FN)
-- ============================================================
CREATE TABLE usuarios (
  id_usuario     INT          NOT NULL AUTO_INCREMENT,
  nome           VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL,
  senha_hash     VARCHAR(255) NOT NULL,
  idade          TINYINT      NOT NULL,
  cidade         VARCHAR(100) DEFAULT NULL,
  estado         CHAR(2)      DEFAULT NULL,
  tem_passaporte TINYINT(1)   NOT NULL DEFAULT 0,
  id_perfil      INT          NOT NULL DEFAULT 2,
  ativo          TINYINT(1)   NOT NULL DEFAULT 1,
  criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT pk_usuarios      PRIMARY KEY (id_usuario),
  CONSTRAINT uq_email         UNIQUE (email),
  CONSTRAINT fk_usu_perfil    FOREIGN KEY (id_perfil)
    REFERENCES perfis(id_perfil)
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: paises
-- Países de destino — separado (3FN): evita repetir nome,
-- moeda e idioma em cada projeto
-- ============================================================
CREATE TABLE paises (
  id_pais        INT           NOT NULL AUTO_INCREMENT,
  nome           VARCHAR(100)  NOT NULL,
  bandeira       VARCHAR(10)   NOT NULL DEFAULT '',
  moeda          VARCHAR(10)   NOT NULL,
  idioma         VARCHAR(80)   NOT NULL,
  requer_visto   TINYINT(1)    NOT NULL DEFAULT 0,
  custo_mensal   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT pk_paises PRIMARY KEY (id_pais)
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: objetivos
-- Tipos de objetivo do intercâmbio (3FN)
-- ============================================================
CREATE TABLE objetivos (
  id_objetivo INT        NOT NULL AUTO_INCREMENT,
  descricao   VARCHAR(60) NOT NULL,
  CONSTRAINT pk_objetivos PRIMARY KEY (id_objetivo)
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: projetos
-- Projeto de intercâmbio do usuário
-- FK para usuarios, paises, objetivos
-- ============================================================
CREATE TABLE projetos (
  id_projeto    INT          NOT NULL AUTO_INCREMENT,
  id_usuario    INT          NOT NULL,
  id_pais       INT          NOT NULL,
  id_objetivo   INT          NOT NULL,
  nome          VARCHAR(150) NOT NULL,
  cidade        VARCHAR(100) NOT NULL,
  duracao_meses TINYINT      NOT NULL,
  data_inicio   DATE         DEFAULT NULL,
  notas         TEXT         DEFAULT NULL,
  criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT pk_projetos      PRIMARY KEY (id_projeto),
  CONSTRAINT fk_proj_usuario  FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_proj_pais     FOREIGN KEY (id_pais)
    REFERENCES paises(id_pais),
  CONSTRAINT fk_proj_objetivo FOREIGN KEY (id_objetivo)
    REFERENCES objetivos(id_objetivo)
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: plano_financeiro
-- Plano financeiro de um projeto (1:1 com projetos)
-- ============================================================
CREATE TABLE plano_financeiro (
  id_plano       INT           NOT NULL AUTO_INCREMENT,
  id_projeto     INT           NOT NULL,
  custo_mensal   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  valor_guardado DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  meta_total     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT pk_plano      PRIMARY KEY (id_plano),
  CONSTRAINT uq_plano_proj UNIQUE (id_projeto),
  CONSTRAINT fk_plano_proj FOREIGN KEY (id_projeto)
    REFERENCES projetos(id_projeto) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: categorias_checklist
-- Categorias dos itens (3FN) — ex: Documentação, Saúde...
-- ============================================================
CREATE TABLE categorias_checklist (
  id_categoria INT        NOT NULL AUTO_INCREMENT,
  nome         VARCHAR(60) NOT NULL,
  CONSTRAINT pk_cat PRIMARY KEY (id_categoria)
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: checklist
-- Itens de checklist de cada projeto
-- FK para projetos e categorias_checklist
-- ============================================================
CREATE TABLE checklist (
  id_item      INT          NOT NULL AUTO_INCREMENT,
  id_projeto   INT          NOT NULL,
  id_categoria INT          NOT NULL,
  titulo       VARCHAR(150) NOT NULL,
  descricao    TEXT         DEFAULT NULL,
  concluido    TINYINT(1)   NOT NULL DEFAULT 0,
  CONSTRAINT pk_checklist    PRIMARY KEY (id_item),
  CONSTRAINT fk_chk_projeto  FOREIGN KEY (id_projeto)
    REFERENCES projetos(id_projeto) ON DELETE CASCADE,
  CONSTRAINT fk_chk_cat      FOREIGN KEY (id_categoria)
    REFERENCES categorias_checklist(id_categoria)
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: timeline
-- Linha do tempo de um projeto
-- FK para projetos e categorias_checklist
-- ============================================================
CREATE TABLE timeline (
  id_evento     INT          NOT NULL AUTO_INCREMENT,
  id_projeto    INT          NOT NULL,
  id_categoria  INT          NOT NULL,
  titulo        VARCHAR(150) NOT NULL,
  descricao     TEXT         DEFAULT NULL,
  data_prevista DATE         NOT NULL,
  concluido     TINYINT(1)   NOT NULL DEFAULT 0,
  CONSTRAINT pk_timeline     PRIMARY KEY (id_evento),
  CONSTRAINT fk_tl_projeto   FOREIGN KEY (id_projeto)
    REFERENCES projetos(id_projeto) ON DELETE CASCADE,
  CONSTRAINT fk_tl_cat       FOREIGN KEY (id_categoria)
    REFERENCES categorias_checklist(id_categoria)
) ENGINE=InnoDB;

-- ============================================================
-- DADOS DE TESTE
-- ============================================================

INSERT INTO perfis (nome) VALUES ('admin'), ('usuario');

INSERT INTO paises (nome, bandeira, moeda, idioma, requer_visto, custo_mensal) VALUES
  ('Canadá',         '🇨🇦', 'CAD', 'Inglês/Francês', 1, 7500.00),
  ('Estados Unidos', '🇺🇸', 'USD', 'Inglês',          1, 9500.00),
  ('Portugal',       '🇵🇹', 'EUR', 'Português',       0, 5500.00),
  ('Austrália',      '🇦🇺', 'AUD', 'Inglês',          1, 10500.00),
  ('Reino Unido',    '🇬🇧', 'GBP', 'Inglês',          1, 11000.00),
  ('Irlanda',        '🇮🇪', 'EUR', 'Inglês',          1, 9000.00),
  ('Alemanha',       '🇩🇪', 'EUR', 'Alemão',          0, 7000.00),
  ('Japão',          '🇯🇵', 'JPY', 'Japonês',         1, 7500.00);

INSERT INTO objetivos (descricao) VALUES
  ('Estudar'), ('Trabalhar'), ('Estudar e Trabalhar'), ('Voluntariado');

INSERT INTO categorias_checklist (nome) VALUES
  ('Documentação'), ('Financeiro'), ('Saúde'), ('Moradia'), ('Viagem'), ('Trabalho');

-- Senha: admin123 (hash gerado pelo setup_senhas.php)
INSERT INTO usuarios (nome, email, senha_hash, idade, cidade, estado, tem_passaporte, id_perfil)
VALUES ('Administrador', 'admin@nextstep.com', 'RODAR_SETUP', 25, 'Campinas', 'SP', 1, 1);

-- Senha: teste123
INSERT INTO usuarios (nome, email, senha_hash, idade, cidade, estado, tem_passaporte, id_perfil)
VALUES ('Ingridy', 'ingridy@nextstep.com', 'RODAR_SETUP', 17, 'Campinas', 'SP', 0, 2);

-- Projeto de teste
INSERT INTO projetos (id_usuario, id_pais, id_objetivo, nome, cidade, duracao_meses, data_inicio, notas)
VALUES (2, 3, 1, 'Intercâmbio em Lisboa', 'Lisboa', 6, '2025-07-01', 'Foco em aperfeiçoar o inglês na Europa');

INSERT INTO plano_financeiro (id_projeto, custo_mensal, valor_guardado, meta_total)
VALUES (1, 5500.00, 8000.00, 33000.00);

INSERT INTO checklist (id_projeto, id_categoria, titulo, descricao) VALUES
  (1, 1, 'Tirar passaporte',          'Agendar na Polícia Federal com antecedência'),
  (1, 1, 'Solicitar visto',           'Portugal: verificar necessidade para brasileiros'),
  (1, 2, 'Comprovação financeira',    'Extrato bancário dos últimos 3 meses'),
  (1, 3, 'Seguro saúde internacional','Cobrir todo o período da viagem'),
  (1, 5, 'Comprar passagem',          'Ida e volta com bagagem incluída'),
  (1, 4, 'Reservar moradia inicial',  'Hostel ou Airbnb para as primeiras 2 semanas');

INSERT INTO timeline (id_projeto, id_categoria, titulo, data_prevista) VALUES
  (1, 1, 'Início do planejamento',   '2025-01-15'),
  (1, 1, 'Solicitar documentos',     '2025-02-15'),
  (1, 5, 'Comprar passagem aérea',   '2025-04-15'),
  (1, 4, 'Reservar moradia',         '2025-05-15'),
  (1, 5, 'Embarque! ✈️',             '2025-07-01');
