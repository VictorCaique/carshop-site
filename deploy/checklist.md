# Checklist de implantação de uma nova loja

Referência: SPEC §10. Meta de **2 a 3 horas** de trabalho técnico depois que o template está pronto,
fora produção de conteúdo e fotos.

---

## Fase 0 — Coleta (antes de tocar em código)

Sem isso completo, **não comece**. O maior atraso desse tipo de projeto é esperar conteúdo do cliente.

- [ ] Logo em PNG/SVG com fundo transparente (se não tiver, cobre à parte para criar)
- [ ] Cores da marca (ou o cliente aceita que você escolha)
- [ ] Endereço completo, telefone fixo, WhatsApp de cada vendedor, horários
- [ ] Instagram / Facebook
- [ ] Texto sobre a loja (ou as respostas de 5 perguntas que você transforma em texto)
- [ ] 3 a 5 fotos da fachada e do showroom
- [ ] Planilha ou lista dos carros do estoque atual

---

## Fase 1 — Instalação (30 min)

- [ ] `deploy/loja.env` preenchido
- [ ] `bash deploy/setup-loja.sh deploy/loja.env` executado sem erro
- [ ] Tema `tema-lv` e plugin `plugin-lv-estoque` ativos
- [ ] Meta Box (gratuito) ativo — campos do veículo aparecem na tela de cadastro
- [ ] Wordfence, UpdraftPlus e WebP Express ativos
- [ ] Permalinks em `/%postname%/`

## Fase 2 — Personalização (45 min)

- [ ] **Identidade:** logo, logo do rodapé, favicon
- [ ] **Aparência:** 3 cores, preset de fonte, fundo claro/escuro, cantos, sombra, largura
- [ ] Se o fundo for escuro ou automático: conferir o logo do rodapé e a foto do hero nos dois temas
- [ ] **Contato:** endereço, mapa embed, telefone, e-mail, CNPJ, horários
- [ ] **Vendedores:** nome + WhatsApp (formato `5511987654321`) de cada um
- [ ] **Conteúdo:** hero, sobre, 3–4 diferenciais, 3 depoimentos
- [ ] Menu principal e menu do rodapé montados
- [ ] Analytics e Pixel preenchidos (só disparam em produção)

## Fase 3 — Estoque (30–60 min)

- [ ] 10 a 15 carros cadastrados
- [ ] 6 marcados como **destaque**
- [ ] Mínimo de 4 fotos por carro (frente 3/4, traseira 3/4, interior/painel, porta-malas)
- [ ] Fotos redimensionadas para 1600px de largura antes do upload
- [ ] Se a loja tem mais de 30 carros: cadastre 15 e ensine o cliente a fazer o resto

## Fase 4 — Publicação (30 min)

- [ ] Domínio apontado, SSL ativo
- [ ] `www` → não-`www` (ou o inverso — escolha um)
- [ ] Sitemap enviado ao Google Search Console
- [ ] Perfil da Empresa no Google criado/atualizado com o link do site
      *(vale mais para a loja do que o site em si — venda os dois juntos)*
- [ ] Link do site na bio do Instagram
- [ ] Backup automático configurado no UpdraftPlus
- [ ] `WP_ENVIRONMENT_TYPE` = `production` no wp-config

## Fase 5 — Entrega (1 hora)

- [ ] Vídeo de 10 min gravado (cadastrar carro, subir fotos, marcar vendido, editar preço)
- [ ] PDF de uma página com login, links e os 4 passos de cadastrar um carro
- [ ] Combinado o que está no suporte e o que é serviço extra

---

## Checklist de aceite

- [ ] Site abre em menos de 3s no 4G
- [ ] Todos os botões de WhatsApp abrem com a mensagem preenchida e o número certo
- [ ] Filtros retornam resultado correto e a URL é compartilhável
- [ ] Carro marcado como vendido some da vitrine e mantém a URL com o selo
- [ ] Nenhum "Lorem ipsum" nem dado da loja anterior
- [ ] Formulário de contato entrega e-mail (teste real, não só "enviou")
- [ ] Favicon, título da aba e compartilhamento no WhatsApp com a imagem correta (OG tags)
- [ ] SSL sem alerta de conteúdo misto
- [ ] **O cliente cadastrou um carro sozinho na sua frente**

---

## Rotina mensal (30 min por site)

- [ ] Atualizar core e plugins — em ambiente de teste primeiro
- [ ] Verificar que o backup é restaurável (não basta existir)
- [ ] Olhar o Search Console
- [ ] Arquivar carros marcados como vendidos há mais de 60 dias
