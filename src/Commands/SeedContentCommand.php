<?php
namespace ChristianoErick\Base\Commands;

use Exception;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FacadeFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use ChristianoErick\Base\Models\Post;
use ChristianoErick\Base\Models\Page;
use ChristianoErick\Base\Models\Category;
use ChristianoErick\Base\Models\Domain;
use ChristianoErick\Base\Models\Image;
use ChristianoErick\Base\Models\Audio;
use ChristianoErick\Base\Models\File;
use ChristianoErick\Base\Models\Tag;

class SeedContentCommand extends Command
{
	protected $signature = 'admin:seed
							{type : Tipo de conteúdo (site|portal)}
							{--count=20 : Quantidade de posts/páginas}
							{--domains=2 : Quantidade de domínios}
							{--with-media : Gerar imagens, áudios e arquivos}
							{--with-videos : Criar posts do tipo vídeo}
							{--with-tags : Gerar e vincular tags}';

	protected $description = 'Gera conteúdo inteligente em português com relacionamentos completos';

	public function handle()
	{
		$type = strtolower($this->argument('type'));

		if (!in_array($type, ['site', 'portal'])) {
			$this->error("❌ Tipo inválido! Use: site ou portal");
			return Command::FAILURE;
		}

		$count = (int) $this->option('count');
		$domainsCount = (int) $this->option('domains');
		$withMedia = $this->option('with-media');
		$withTags = $this->option('with-tags');

		$this->info("🚀 Gerando conteúdo para: " . strtoupper($type));
		$this->newLine();

		//DB::beginTransaction();

		try {
			// 1. Criar domínios
			$domains = $this->createDomains($domainsCount);

			// 2. Criar tags se solicitado
			$tags = $withTags ? $this->createTags() : collect();

			// 3. Criar mídia se solicitado
			$images = $withMedia ? $this->createImages() : collect();
			$audios = $withMedia ? $this->createAudios() : collect();
			$files = $withMedia ? $this->createFiles() : collect();
			//$videos = $withMedia ? $this->createVideos() : collect();

			// 4. Gerar conteúdo baseado no tipo
			match($type) {
				'site' => $this->generateSiteContent($domains),
				'portal' => $this->generatePortalContent($count, $domains, $tags, $images, $files),
			};

			//DB::commit();

			$this->newLine();
			$this->info('✅ Conteúdo gerado com sucesso!');
			$this->displaySummary($type, $count, $domains, $tags, $images, $audios, $files);

			return Command::SUCCESS;

		} catch (Exception $e) {
			//DB::rollBack();
			$this->error("❌ Erro ao gerar conteúdo: " . $e->getMessage());
			return Command::FAILURE;
		}
	}

	protected function createDomains($count)
	{
		return $this->components->task('Criando domínios', function () use ($count, &$domains) {
			$domains = collect();
			foreach (range(1, $count) as $i) {
				$item = Domain::find($i);
				if (!is_object($item)) {
					$item = Domain::create([
						'status' => true,
						'name' => "Domínio {$i}",
						'domain' => "dominio-{$i}.ddev.site",
					]);
				}
				$domains->push($item);
			}
			return $domains;
		});
	}

	protected function createTags()
	{
		return $this->components->task('Criando tags', function () {
			$tags = collect();
			foreach ($this->tags as $tagName) {
				$tags->push(Tag::firstOrCreate(
					['slug' => Str::slug($tagName)],
					['tag' => $tagName]
				));
			}
			return $tags;
		});
	}

	protected function createImages()
	{
		return $this->components->task('Criando imagens', function () {
			$images = collect();
			foreach ($this->images as $image)
			{
				$item = Image::firstWhere('file', $image);
				if (!is_object($item))
				{
					try
					{
						$path = 'images/'.$image;

						$extension = pathinfo($path, PATHINFO_EXTENSION);

						$size = Storage::size($path);

						$mime = Storage::mimeType($path);

						$stream = Storage::readStream($path);
						$hash = hash_init('sha1');
						hash_update_stream($hash, $stream);
						$sha1 = hash_final($hash);
						fclose($stream);

						$item = Image::create([
							'ai' => false,
							'caption' => 'Imagem Ilustrativa',
							'author' => 'Sistema',
							'hash' => $sha1,
							'file' => $image,
							'file_data' => [
								'ext' => $extension,
								'mime' => $mime,
								'size' => $size,
							],
						]);
					} catch(Exception $e) { }
				}

				if (is_object($item))
				{
					$images->push($item);
				}
			}

			return $images;
		});
	}

	protected function createAudios()
	{
		return $this->components->task('Criando áudios', function () {
			$audios = collect();
			foreach (range(1, 50) as $n) {
				$n = ($n < 10 ? '0' : '').$n.'.mp3';
				$item = Audio::firstWhere('file', $n);
				if (!is_object($item))
				{
					try
					{
						$path = 'audios/'.$n;

						$extension = pathinfo($path, PATHINFO_EXTENSION);

						$size = Storage::size($path);

						$mime = Storage::mimeType($path);

						$stream = Storage::readStream($path);
						$hash = hash_init('sha1');
						hash_update_stream($hash, $stream);
						$sha1 = hash_final($hash);
						fclose($stream);

						$item = Audio::create([
							'ai' => false,
							'caption' => 'Audio Ilustrativo',
							'hash' => $sha1,
							'file' => $n,
							'file_data' => [
								'ext' => $extension,
								'mime' => $mime,
								'size' => $size,
							],
						]);
					} catch(Exception $e) { }
				}

				if (is_object($item))
				{
					$audios->push($item);
				}
			}

			return $audios;
		});
	}

	protected function createVideos()
	{
		return $this->components->task('Criando vídeos', function () {
			$audios = collect();
			foreach (range(1, 50) as $n) {
				$n = ($n < 10 ? '0' : '').$n.'.mp3';
				$item = Audio::firstWhere('file', $n);
				if (!is_object($item))
				{
					try
					{
						$path = 'audios/'.$n;

						$extension = pathinfo($path, PATHINFO_EXTENSION);

						$size = Storage::size($path);

						$mime = Storage::mimeType($path);

						$stream = Storage::readStream($path);
						$hash = hash_init('sha1');
						hash_update_stream($hash, $stream);
						$sha1 = hash_final($hash);
						fclose($stream);

						$item = Audio::create([
							'ai' => false,
							'caption' => 'Audio Ilustrativo',
							'hash' => $sha1,
							'file' => $n,
							'file_data' => [
								'ext' => $extension,
								'mime' => $mime,
								'size' => $size,
							],
						]);
					} catch(Exception $e) { }
				}

				if (is_object($item))
				{
					$audios->push($item);
				}
			}

			return $audios;
		});
	}

	protected function createFiles()
	{
		return $this->components->task('Criando arquivos', function () {
			$files = collect();
			foreach ($this->files as $file)
			{
				$item = File::firstWhere('file', $file);
				if (!is_object($item))
				{
					try
					{
						$path = 'files/'.$file;

						$extension = pathinfo($path, PATHINFO_EXTENSION);

						$size = Storage::size($path);

						$mime = Storage::mimeType($path);

						$stream = Storage::readStream($path);
						$hash = hash_init('sha1');
						hash_update_stream($hash, $stream);
						$sha1 = hash_final($hash);
						fclose($stream);

						$item = File::create([
							'ai' => false,
							'caption' => 'Arquivo Ilustrativo',
							'hash' => $sha1,
							'file' => $file,
							'file_data' => [
								'ext' => $extension,
								'mime' => $mime,
								'size' => $size,
							],
						]);
					} catch(Exception $e) { }
				}

				if (is_object($item))
				{
					$files->push($item);
				}
			}

			return $files;
		});
	}

	protected function generateSiteContent($domains)
	{
		$pages = [
			['title' => 'Início', 'content' => 'Bem-vindo ao nosso site institucional.'],
			['title' => 'Sobre Nós', 'content' => 'Conheça nossa história e valores.'],
			['title' => 'Serviços', 'content' => 'Veja todos os serviços que oferecemos.'],
			['title' => 'Produtos', 'content' => 'Confira nosso catálogo de produtos.'],
			['title' => 'Contato', 'content' => 'Entre em contato conosco.'],
			['title' => 'Política de Privacidade', 'content' => 'Nossa política de privacidade.'],
		];

		$bar = $this->output->createProgressBar(count($pages));
		$bar->start();

		foreach ($pages as $pageData) {
			$page = Page::create([
				'title' => $pageData['title'],
				'slug' => Str::slug($pageData['title']),
				'content' => $this->generateContent($pageData['content'], 500),
				'is_active' => true,
				'published_at' => now()
			]);

			// Vincular a domínios
			$page->domains()->attach($domains->random(rand(1, $domains->count()))->pluck('id'));

			$bar->advance();
		}

		$bar->finish();
		$this->newLine();
	}

	protected function generatePortalContent($count, $domains, $tags, $images, $files)
	{
		$categories = $this->createCategories($this->newsCategories, 'noticias');

		$bar = $this->output->createProgressBar($count);
		$bar->start();

		for ($i = 0; $i < $count; $i++) {
			$category = $categories->random();
			$title = str_replace('{categoria}', strtolower($category->name), $this->newsTitles[array_rand($this->newsTitles)]);

			$post = Post::create([
				'title' => $title,
				'slug' => Str::slug($title) . '-' . uniqid(),
				'excerpt' => $this->generateExcerpt(),
				'content' => $this->generateContent('notícia', rand(800, 2000)),
				'type' => 'noticias',
				'status' => 'published',
				'published_at' => now()->subDays(rand(0, 30)),
				'author' => $this->getRandomAuthor()
			]);

			$this->attachRelations($post, $categories->random(rand(1, 3)), $domains, $tags, $images, $files, null);

			$bar->advance();
		}

		$bar->finish();
		$this->newLine();
	}

	protected function createCategories($categoryNames, $type)
	{
		$categories = collect();

		foreach ($categoryNames as $name) {
			$category = Category::firstOrCreate([
				'slug' => Str::slug($name),
				'type' => $type
			], [
				'name' => $name,
				'description' => "Categoria de {$name}"
			]);

			$categories->push($category);
		}

		return $categories;
	}

	protected function attachRelations($post, $categories, $domains, $tags, $images, $files, $audios)
	{
		// Categorias
		$post->categories()->attach($categories->pluck('id'));

		// Domínios
		if ($domains->isNotEmpty()) {
			$post->domains()->attach($domains->random(rand(1, min(2, $domains->count())))->pluck('id'));
		}

		// Tags
		if ($tags->isNotEmpty()) {
			$post->tags()->attach($tags->random(rand(3, 7))->pluck('id'));
		}

		// Imagens
		if ($images->isNotEmpty()) {
			$post->images()->attach($images->random(rand(1, 5))->pluck('id'));
		}

		// Arquivos
		if ($files && $files->isNotEmpty() && rand(0, 1)) {
			$post->files()->attach($files->random(rand(1, 2))->pluck('id'));
		}

		// Áudios
		if ($audios && $audios->isNotEmpty() && rand(0, 1)) {
			$post->audios()->attach($audios->random()->id);
		}
	}

	protected function generateExcerpt()
	{
		$excerpts = [
			'Este conteúdo traz informações relevantes e atualizadas sobre o tema.',
			'Descubra insights importantes e análises detalhadas neste artigo.',
			'Uma análise completa e atualizada sobre o assunto.',
			'Confira os detalhes e entenda melhor esta questão.',
			'Tudo o que você precisa saber sobre este tema importante.'
		];

		return $excerpts[array_rand($excerpts)];
	}

	protected function generateContent($context, $words = 1000)
	{
		$paragraphs = [];
		$paragraphCount = ceil($words / 100);

		for ($i = 0; $i < $paragraphCount; $i++) {
			$paragraphs[] = "Este é um parágrafo de exemplo gerado automaticamente para {$context}. " .
						   "O conteúdo aqui apresentado serve como placeholder e deve ser substituído por texto real. " .
						   "Em um cenário de produção, este texto seria gerado dinamicamente ou obtido de uma fonte de dados apropriada. " .
						   "É importante manter a qualidade e relevância do conteúdo para garantir uma boa experiência do usuário.";
		}

		return implode("\n\n", $paragraphs);
	}

	protected function getRandomAuthor()
	{
		$authors = [
			'João Silva', 'Maria Santos', 'Pedro Oliveira', 'Ana Costa',
			'Carlos Ferreira', 'Juliana Lima', 'Rafael Souza', 'Fernanda Alves'
		];

		return $authors[array_rand($authors)];
	}

	protected function getMimeType($extension)
	{
		return match($extension) {
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'zip' => 'application/zip',
			default => 'application/octet-stream'
		};
	}

	protected function displaySummary($type, $count, $domains, $tags, $images, $audios, $files)
	{
		$this->table(
			['Item', 'Quantidade'],
			[
				['Tipo de Conteúdo', strtoupper($type)],
				['Posts/Páginas', $count],
				['Domínios', $domains->count()],
				['Tags', $tags->count()],
				['Imagens', $images->count()],
				['Áudios', $audios->count()],
				['Arquivos', $files->count()],
			]
		);
	}



	protected $newsCategories = [
		'Política',
		'Economia',
		'Tecnologia',
		'Esportes',
		'Entretenimento',
		'Cultura',
		'Saúde',
		'Educação',
		'Ciência',
		'Meio Ambiente',
	];

	protected $blogCategories = [
		'Lifestyle',
		'Viagens',
		'Gastronomia',
		'Moda',
		'Decoração',
		'Finanças Pessoais',
		'Produtividade',
		'Desenvolvimento Pessoal',
	];

	protected $tvCategories = [
		'Jornalismo',
		'Entretenimento',
		'Documentários',
		'Séries',
		'Entrevistas',
		'Debates',
		'Programas Infantis',
	];

	protected $newsTitles = [
		'Nova lei aprovada no Congresso pode mudar {categoria}',
		'Especialistas debatem futuro de {categoria} no Brasil',
		'Governo anuncia investimentos bilionários em {categoria}',
		'Pesquisa revela tendências alarmantes em {categoria}',
		'Brasil avança 5 posições no ranking global de {categoria}',
		'Crise em {categoria}: Entenda os impactos no seu bolso',
		'Startup brasileira revoluciona o mercado de {categoria}',
		'O que esperar de {categoria} para o próximo ano?',
		'Entrevista exclusiva: Ministro fala sobre {categoria}',
		'Gigantes da tecnologia apostam tudo em {categoria}',
		'Protestos marcam votação sobre regras de {categoria}',
		'Dólar alto impacta diretamente o setor de {categoria}',
		'Novo estudo de Harvard derruba mitos sobre {categoria}',
		'Fusão de empresas promete agitar o mundo de {categoria}',
		'Escândalo em {categoria} gera repercussão internacional',
		'Evento em São Paulo reúne líderes de {categoria}',
		'Como a Inteligência Artificial está transformando {categoria}',
		'Relatório aponta crescimento recorde em {categoria}',
		'Setor de {categoria} abre milhares de vagas de emprego',
		'Anvisa aprova novas normas para {categoria}',
		'Sustentabilidade vira prioridade em empresas de {categoria}',
		'Fraudes em {categoria} causam prejuízo milionário',
		'Mercado de {categoria} reage positivamente à nova medida',
		'Bolsa de Valores: Ações de {categoria} disparam',
		'China e EUA disputam hegemonia em {categoria}',
		'Pequenos negócios de {categoria} ganham incentivo fiscal',
		'Consumidores reclamam de alta nos preços de {categoria}',
		'O fim de uma era? Mudanças drásticas em {categoria}',
		'Documentário polêmico expõe bastidores de {categoria}',
		'Avanço científico promete mudar a história de {categoria}',
		'Legislativo discute urgência em pauta de {categoria}',
		'Regiões Norte e Nordeste lideram expansão em {categoria}',
		'União Europeia impõe barreiras para {categoria} brasileira',
		'Startups de {categoria} atraem investidores anjo',
		'Hacker vaza dados sigilosos sobre {categoria}',
		'Eleições podem definir o destino de {categoria}',
		'Clima extremo afeta produção e serviços de {categoria}',
		'Google anuncia nova ferramenta voltada para {categoria}',
		'Brasileiros gastam mais com {categoria} em 2024',
		'Especialista alerta para bolha no mercado de {categoria}',
		'Histórico: Mulher assume liderança global em {categoria}',
		'Prefeitura lança programa de fomento à {categoria}',
		'Justiça suspende liminar que afetava {categoria}',
		'Exportações de {categoria} batem recorde histórico',
		'Falência de gigante de {categoria} choca o mercado',
		'Nova variante impacta retomada de {categoria}',
		'Conferência da ONU traz diretrizes para {categoria}',
		'Aplicativo de {categoria} vira febre nacional',
		'Investigação aponta cartel no setor de {categoria}',
		'O impacto do 5G no desenvolvimento de {categoria}',
	];

	protected $blogTitles = [
		'Como melhorar sua {categoria}: 10 dicas práticas',
		'O guia definitivo de {categoria} para iniciantes',
		'Minha experiência real com {categoria}: Vale a pena?',
		'5 erros comuns em {categoria} (e como evitá-los)',
		'Transforme sua visão sobre {categoria} em 30 dias',
		'Por que ninguém te contou isso sobre {categoria}?',
		'O segredo para dominar {categoria} de uma vez por todas',
		'Checklist: Tudo o que você precisa para começar em {categoria}',
		'As 7 melhores ferramentas para quem ama {categoria}',
		'Mitos e Verdades sobre {categoria} que você precisa saber',
		'{categoria}: Um hobby ou uma profissão?',
		'Como economizar dinheiro investindo em {categoria}',
		'Passo a passo: Dominando {categoria} do zero',
		'Entenda a psicologia por trás de {categoria}',
		'3 livros essenciais para entender {categoria}',
		'O que aprendi após 5 anos trabalhando com {categoria}',
		'Como explicar {categoria} para seus avós',
		'A história não contada de {categoria}',
		'{categoria} para preguiçosos: O caminho mais fácil',
		'Faça você mesmo: Projetos incríveis de {categoria}',
		'As tendências de {categoria} que vão bombar este ano',
		'Estudo de caso: Como fulano venceu em {categoria}',
		'Pare de perder tempo com {categoria} da forma errada',
		'O futuro de {categoria}: Minhas previsões',
		'Quiz: O quanto você realmente sabe sobre {categoria}?',
		'Como monetizar seu conhecimento em {categoria}',
		'A relação surpreendente entre {categoria} e felicidade',
		'Desafio de 7 dias: Focando em {categoria}',
		'Por que {categoria} é mais importante do que você pensa',
		'Os 10 mandamentos de quem pratica {categoria}',
		'Review honesto: O melhor curso de {categoria}',
		'Como balancear vida pessoal e {categoria}',
		'Ideias criativas para inovar em {categoria}',
		'O glossário completo de termos de {categoria}',
		'Depoimentos inspiradores sobre {categoria}',
		'Como convencer seu chefe a investir em {categoria}',
		'{categoria} no home office: Dicas de produtividade',
		'O lado sombrio de {categoria} que ninguém mostra',
		'Comparativo: Método A vs Método B em {categoria}',
		'Infográfico: A evolução de {categoria} no tempo',
		'Como criar uma rotina focada em {categoria}',
		'Os maiores influenciadores de {categoria} para seguir',
		'Podcast: Batendo um papo sobre {categoria}',
		'Resumo da semana: O que rolou em {categoria}',
		'Tutorial avançado de {categoria} para experts',
		'Como superar o bloqueio criativo em {categoria}',
		'Dicas de segurança para quem atua em {categoria}',
		'A ciência explica: Os benefícios de {categoria}',
		'Minimalismo e {categoria}: Como unir os dois?',
		'Carta aberta aos amantes de {categoria}',
	];

	protected $tags = [
		'Tutorial',
		'Guia Completo',
		'Dicas Práticas',
		'Análise',
		'Opinião',
		'Entrevista',
		'Reportagem Especial',
		'Bastidores',
		'Ao Vivo',
		'Infográfico',
		'Podcast',
		'Vídeo',
		'Resenha',
		'Estudo de Caso',
		'Passo a Passo',
		'Breaking News',
		'Exclusivo',
		'Últimas Notícias',
		'Em Alta',
		'Tendência',
		'Novidade',
		'Destaque',
		'Plantão',
		'Urgente',
		'Atualização',
		'Brasil',
		'Mundo',
		'Política',
		'Economia',
		'Tecnologia',
		'Inovação',
		'Ciência',
		'Saúde',
		'Educação',
		'Cultura',
		'Esportes',
		'Entretenimento',
		'Meio Ambiente',
		'Justiça',
		'Segurança Pública',
		'Mercado Financeiro',
		'Marketing Digital',
		'Carreira',
		'Empreendedorismo',
		'Startups',
		'Investimentos',
		'Gestão',
		'Liderança',
		'Produtividade',
		'Bem-estar',
	];

	protected $images = [
		'001.jpg',
		'002.jpg',
		'003.jpg',
		'004.jpg',
		'005.jpg',
		'006.jpg',
		'007.jpg',
		'008.jpg',
		'009.jpg',
		'010.avif',
		'011.avif',
		'012.avif',
		'013.jpg',
		'014.jpg',
		'015.jpg',
		'016.jpg',
		'017.jpg',
		'018.jpg',
		'019.avif',
		'020.avif',
		'021.avif',
		'022.jpg',
		'023.jpg',
		'024.jpg',
		'025.jpg',
		'026.avif',
		'027.jpg',
		'028.jpg',
		'029.avif',
		'030.jpg',
		'031.avif',
		'032.avif',
		'033.avif',
		'034.jpg',
		'035.jpg',
		'036.jpg',
		'037.jpg',
		'038.jpg',
		'039.jpg',
		'040.jpg',
		'041.jpg',
		'042.jpg',
		'043.avif',
		'044.jpg',
		'045.avif',
		'046.avif',
		'047.jpg',
		'048.jpg',
		'049.jpg',
		'050.jpg',
		'051.jpg',
		'052.avif',
		'053.jpg',
		'054.jpg',
		'055.jpg',
		'056.jpg',
		'057.jpg',
		'058.jpg',
		'059.jpg',
		'060.jpg',
		'061.jpg',
		'062.jpg',
		'063.avif',
		'064.jpg',
		'065.avif',
		'066.jpg',
		'067.jpg',
		'068.jpg',
		'069.jpg',
		'070.jpg',
		'071.jpg',
		'072.avif',
		'073.jpg',
		'074.avif',
		'075.avif',
		'076.jpg',
		'077.avif',
		'078.jpg',
		'079.jpg',
		'080.jpg',
		'081.avif',
		'082.avif',
		'083.avif',
		'084.jpg',
		'085.jpg',
		'086.jpg',
		'087.jpg',
		'088.avif',
		'089.jpg',
		'090.avif',
		'091.jpg',
		'092.avif',
		'092.jpg',
		'093.jpg',
		'094.jpg',
		'095.jpg',
		'096.avif',
		'097.jpg',
		'099.jpg',
		'100.jpg',
	];

	protected $files = [
		'01.pdf',
		'02.pdf',
		'03.pdf',
		'04.pdf',
		'05.pdf',
		'06.pdf',
		'07.pdf',
		'08.pdf',
		'09.pdf',
		'10.pdf',
		'11.ppt',
		'12.ppt',
		'13.ppt',
		'14.xls',
		'15.xls',
		'16.xls',
		'17.xls',
		'18.xls',
		'19.doc',
		'20.docx',
		'21.doc',
		'22.docx',
		'23.doc',
		'24.docx',
		'25.doc',
		'26.docx',
		'27.csv',
		'28.csv',
		'29.csv',
		'30.zip',
		'31.zip',
		'32.zip',
		'33.txt',
		'34.txt',
		'35.txt',
		'36.txt',
		'37.txt',
		'38.rar',
		'39.rar',
		'40.rar',
		'41.rar',
		'42.rar',
		'43.7z',
		'44.gz',
		'45.gz',
		'46.gz',
		'47.gz',
		'48.gz',
		'49.tar',
		'50.tar',
	];

	$videos = [
		['id' => 'zgaCZOQCpp8', 'title' => 'Lady Gaga, Bruno Mars - Die With A Smile (Lyrics)'],
		['id' => 'u2ah9tWTkmk', 'title' => 'Alex Warren - Ordinary (Official Video)'],
		['id' => 'hT_nvWreIhg', 'title' => 'OneRepublic - Counting Stars'],
		['id' => '9gWIIIr2Asw', 'title' => 'Teddy Swims - Lose Control (The Village Sessions)'],
		['id' => 'V9PVRfjEBTI', 'title' => 'Billie Eilish - BIRDS OF A FEATHER (Official Music Video)'],
		['id' => 'dT2owtxkU8k', 'title' => "Shawn Mendes - There's Nothing Holdin' Me Back (Official Music Video)"],
		['id' => 'zABLecsR5UE', 'title' => 'Lewis Capaldi - Someone You Loved'],
		['id' => 'ekr2nIex040', 'title' => 'ROSÉ & Bruno Mars - APT. (Official Music Video)'],
		['id' => '1el2U3f7Y18', 'title' => 'Teddy Swims - Devil in a Dress (Live)'],
		['id' => 'yebNIHKAC4A', 'title' => '“Golden” Official Lyric Video | KPop Demon Hunters | Sony Animation'],
		['id' => 'EkHTsc9PU2A', 'title' => "Jason Mraz - I'm Yours (Official Video) [4K Remaster]"],
		['id' => 'f4Y3b7un4LE', 'title' => 'Benson Boone - Slow It Down (Official Music Video)'],
		['id' => 'xGaZBfJOyAc', 'title' => 'Lady Gaga - The Dead Dance (Official Music Video)'],
		['id' => 'orJSJGHjBLI', 'title' => 'Ed Sheeran - Bad Habits [Official Video]'],
		['id' => 'L3wKzyIN1yk', 'title' => "Rag'n'Bone Man - Human (Official Video)"],
		['id' => 'fLexgOxsZu0', 'title' => 'Bruno Mars - The Lazy Song (Official Music Video)'],
		['id' => 'qN4ooNx77u0', 'title' => 'Harry Styles - Sign of the Times (Official Video)'],
		['id' => 'zaIsVnmwdqg', 'title' => 'Kygo - Happy Now ft. Sandro Cavazza (Official Video)'],
		['id' => 'CevxZvSJLk8', 'title' => 'Katy Perry - Roar'],
		['id' => 'Qh8QwVYOSVU', 'title' => 'Teddy Swims - Bad Dreams (Official Music Video)'],
		['id' => 'tKml80alH3Y', 'title' => 'Myles Smith - Stargazing (Lyric Video)'],
		['id' => 'JgDNFQ2RaLQ', 'title' => 'Ed Sheeran - Sapphire (Official Music Video)'],
		['id' => 'RgKAFK5djSk', 'title' => 'Wiz Khalifa - See You Again ft. Charlie Puth [Official Video] Furious 7 Soundtrack'],
		['id' => 'JGwWNGJdvx8', 'title' => 'Ed Sheeran - Shape of You (Official Music Video)'],
		['id' => 'cBVGlBWQzuc', 'title' => 'Maroon 5 - Girls Like You ft. Cardi B (Volume 2) (Official Music Video)'],
		['id' => 'ShZ978fBl6Y', 'title' => 'Calum Scott - You Are The Reason (Official Video)'],
		['id' => '2Vv-BfVoq4g', 'title' => 'Ed Sheeran - Perfect (Official Music Video)'],
		['id' => 'VPRjCeoBqrI', 'title' => 'Coldplay - A Sky Full Of Stars (Official Video)'],
		['id' => '983bBbJx0Mk', 'title' => '"Soda Pop" Official Lyric Video | KPop Demon Hunters | Sony Animation'],
		['id' => 'GR3Liudev18', 'title' => 'Chappell Roan - Pink Pony Club (Official Music Video)'],
		['id' => 'lY2yjAdbvdQ', 'title' => 'Shawn Mendes - Treat You Better'],
		['id' => 'HPR-VwzbDRg', 'title' => 'Benson Boone - In The Stars (Official Music Video)'],
		['id' => 'G7KNmW9a75Y', 'title' => 'Miley Cyrus - Flowers (Official Video)'],
		['id' => 'FM7MFYoylVs', 'title' => 'The Chainsmokers & Coldplay - Something Just Like This (Official Lyric Video)'],
		['id' => '09R8_2nJtjg', 'title' => 'Maroon 5 - Sugar (Official Music Video)'],
		['id' => 'LHCob76kigA', 'title' => 'Lukas Graham - 7 Years [Official Music Video]'],
		['id' => 'lp-EO5I60KA', 'title' => 'Ed Sheeran - Thinking Out Loud (Official Music Video)'],
		['id' => 'OPf0YbXqDm0', 'title' => 'Mark Ronson - Uptown Funk (Official Video) ft. Bruno Mars'],
		['id' => 'eVli-tstM5E', 'title' => 'Sabrina Carpenter - Espresso'],
		['id' => 'Oextk-If8HQ', 'title' => 'Keane - Somewhere Only We Know (Official Music Video)'],
		['id' => 'wAjHQXrIj9o', 'title' => 'Bad Bunny ft. Bomba Estéreo - Ojitos Lindos (Video Oficial) | Un Verano Sin Ti'],
		['id' => '8UVNT4wvIGY', 'title' => 'Gotye - Somebody That I Used To Know (feat. Kimbra) [Official Music Video]'],
		['id' => 'ru0K8uYEZWw', 'title' => 'Justin Timberlake - CAN\'T STOP THE FEELING! (from DreamWorks Animation\'s "TROLLS") (Official Video)'],
		['id' => 'Oa_RSwwpPaA', 'title' => 'Benson Boone - Beautiful Things (Official Music Video)'],
		['id' => 'aSugSGCC12I', 'title' => 'Sabrina Carpenter - Manchild (Official Video)'],
		['id' => 'Lo4_K4relMg', 'title' => 'Rosa Linn - Snap - (Official Eurovision Music Video)'],
		['id' => 'kffacxfA7G4', 'title' => 'Justin Bieber - Baby ft. Ludacris'],
		['id' => 'Ek0SgwWmF9w', 'title' => 'Muse - Madness'],
		['id' => '0-7IHOXkiV8', 'title' => 'KALEO - Way Down We Go (Official Music Video)'],
		['id' => '9bZkp7q19f0', 'title' => 'PSY - GANGNAM STYLE(강남스타일) M/V']
	];
}
