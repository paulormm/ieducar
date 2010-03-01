<?php

/**
 * i-Educar - Sistema de gest�o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itaja�
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa � software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * sob os termos da Licen�a P�blica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licen�a, como (a seu crit�rio)
 * qualquer vers�o posterior.
 *
 * Este programa � distribu��do na expectativa de que seja �til, por�m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia impl��cita de COMERCIABILIDADE OU
 * ADEQUA��O A UMA FINALIDADE ESPEC�FICA. Consulte a Licen�a P�blica Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endere�o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     App_Model
 * @subpackage  UnitTests
 * @since       Arquivo dispon�vel desde a vers�o 1.1.0
 * @version     $Id$
 */

require_once 'App/Model/IedFinder.php';
require_once 'include/pmieducar/clsPmieducarInstituicao.inc.php';
require_once 'include/pmieducar/clsPmieducarSerie.inc.php';
require_once 'include/pmieducar/clsPmieducarMatricula.inc.php';
require_once 'include/pmieducar/clsPmieducarMatriculaTurma.inc.php';
require_once 'include/pmieducar/clsPmieducarEscolaSerieDisciplina.inc.php';
require_once 'include/pmieducar/clsPmieducarEscolaAnoLetivo.inc.php';
require_once 'include/pmieducar/clsPmieducarAnoLetivoModulo.inc.php';
require_once 'RegraAvaliacao/Model/RegraDataMapper.php';
require_once 'FormulaMedia/Model/FormulaDataMapper.php';
require_once 'TabelaArredondamento/Model/TabelaDataMapper.php';
require_once 'TabelaArredondamento/Model/TabelaValorDataMapper.php';
require_once 'ComponenteCurricular/Model/ComponenteDataMapper.php';
require_once 'ComponenteCurricular/Model/AnoEscolarDataMapper.php';
require_once 'AreaConhecimento/Model/AreaDataMapper.php';

/**
 * App_Model_IedFinderTest class.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     App_Model
 * @subpackage  UnitTests
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @version     @@package_version@@
 */
class App_Model_IedFinderTest extends UnitBaseTest
{
  /**
   * @todo Refatorar m�todo para uma classe stub, no diret�rio do m�dulo
   *   TabelaArredondamento
   * @todo Est� copiado em modules/Avaliacao/_tests/BoletimTest.php
   */
  protected function _getTabelaArredondamento()
  {
    $data = array(
      'tabelaArredondamento' => 1,
      'nome'                 => NULL,
      'descricao'            => NULL,
      'valorMinimo'          => -1,
      'valorMaximo'          => 0
    );

    $tabelaValores = array();
    for ($i = 0; $i <= 10; $i++) {
      $data['nome'] = $i;
      $data['valorMinimo'] += 1;
      $data['valorMaximo'] += 1;

      if ($i == 10) {
        $data['valorMinimo'] = 9;
        $data['valorMaximo'] = 10;
      }

      $tabelaValores[$i] = new TabelaArredondamento_Model_TabelaValor($data);
    }

    $mapperMock = $this->getCleanMock('TabelaArredondamento_Model_TabelaValorDataMapper');
    $mapperMock->expects($this->any())
               ->method('findAll')
               ->will($this->returnValue($tabelaValores));

    $tabelaDataMapper = new TabelaArredondamento_Model_TabelaDataMapper();
    $tabelaDataMapper->setTabelaValorDataMapper($mapperMock);

    $tabela = new TabelaArredondamento_Model_Tabela(array('nome' => 'Num�ricas'));
    $tabela->setDataMapper($tabelaDataMapper);
    return $tabela;
  }

  public function testCarregaNomeDeCursoPorCodigo()
  {
    $returnValue = array(
      'nm_curso' => 'Ensino Fundamental'
    );

    $mock = $this->getCleanMock('clsPmieducarCurso');
    $mock->expects($this->once())
         ->method('detalhe')
         ->will($this->returnValue($returnValue));

    // Registra a inst�ncia no reposit�rio de classes de CoreExt_Entity
    $instance = App_Model_IedFinder::addClassToStorage(
      'clsPmieducarCurso', $mock, NULL, TRUE);

    $curso = App_Model_IedFinder::getCurso(1);
    $this->assertEquals($returnValue['nm_curso'], $curso);
  }

  public function testCarregaSeries()
  {
    $returnValue = array(1 => array('cod_serie' => 1, 'nm_serie' => 'Pr�'));

    $mock = $this->getCleanMock('clsPmieducarSerie');
    $mock->expects($this->once())
         ->method('lista')
         ->will($this->returnValue($returnValue));

    // Registra a inst�ncia no reposit�rio de classes de CoreExt_Entity
    $instance = CoreExt_Entity::addClassToStorage(
      'clsPmieducarSerie', $mock, NULL, TRUE);

    $series = App_Model_IedFinder::getSeries(1);
    $this->assertEquals($returnValue, $series);
  }

  public function testCarregaInstituicoes()
  {
    $returnValue = array(array('cod_instituicao' => 1, 'nm_instituicao' => 'Institui��o'));
    $expected = array(1 => 'Institui��o');

    $mock = $this->getCleanMock('clsPmieducarInstituicao');
    $mock->expects($this->once())
         ->method('lista')
         ->will($this->returnValue($returnValue));

    // Registra a inst�ncia no reposit�rio de classes de CoreExt_Entity
    $instance = App_Model_IedFinder::addClassToStorage(
      'clsPmieducarInstituicao', $mock);

    $instituicoes = App_Model_IedFinder::getInstituicoes();
    $this->assertEquals($expected, $instituicoes);
  }

  public function testGetMatricula()
  {
    $expected = array(
      'cod_matricula'       => 1,
      'ref_ref_cod_serie'   => 1,
      'ref_ref_cod_escola'  => 1,
      'ref_cod_curso'       => 1,
      'curso_carga_horaria' => 800,
      'curso_hora_falta'    => (50 /60),
      'serie_carga_horaria' => 800,
      'curso_nome'          => '',
      'serie_nome'          => '',
      'serie_concluinte'    => ''
    );

    $returnMatricula = array('cod_matricula' => 1, 'ref_ref_cod_serie' => 1, 'ref_ref_cod_escola' => 1, 'ref_cod_curso' => 1);
    $returnSerie = array('cod_serie' => 1, 'carga_horaria' => 800, 'regra_avaliacao_id' => 1);
    $returnCurso = array('cod_curso' => 1, 'carga_horaria' => 800, 'hora_falta' => (50 / 60), 'padrao_ano_escolar' => 1);

    $matriculaMock = $this->getCleanMock('clsPmieducarMatricula');
    $matriculaMock->expects($this->once())
                  ->method('detalhe')
                  ->will($this->returnValue($returnMatricula));

    $serieMock = $this->getCleanMock('clsPmieducarSerie');
    $serieMock->expects($this->any())
              ->method('detalhe')
              ->will($this->returnValue($returnSerie));

    $cursoMock = $this->getCleanMock('clsPmieducarCurso');
    $cursoMock->expects($this->any())
              ->method('detalhe')
              ->will($this->returnValue($returnCurso));

    CoreExt_Entity::addClassToStorage('clsPmieducarMatricula', $matriculaMock, NULL, TRUE);
    CoreExt_Entity::addClassToStorage('clsPmieducarSerie', $serieMock, NULL, TRUE);
    CoreExt_Entity::addClassToStorage('clsPmieducarCurso', $cursoMock, NULL, TRUE);

    $matricula = App_Model_IedFinder::getMatricula(1);
    $this->assertEquals($expected, $matricula);
  }

  public function testInstanciaRegraDeAvaliacaoPorMatricula()
  {
    $expected = new RegraAvaliacao_Model_Regra(array(
      'id'                   => 1,
      'nome'                 => 'Regra geral',
      'tipoNota'             => RegraAvaliacao_Model_Nota_TipoValor::NUMERICA,
      'tipoProgressao'       => RegraAvaliacao_Model_TipoProgressao::CONTINUADA,
      'tipoPresenca'         => RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE,
      'media'                => 6,
      'tabelaArredondamento' => $this->_getTabelaArredondamento()
    ));

    // Marca como "old", para indicar que foi recuperado via CoreExt_DataMapper
    $expected->markOld();

    // Retorna para matr�cula
    $returnMatricula = array(
      'cod_matricula'      => 1,
      'ref_ref_cod_escola' => 1,
      'ref_ref_cod_serie'  => 1,
      'ref_cod_curso'      => 1,
      'aprovado'           => 1
    );

    // Mock para clsPmieducarMatricula
    $matriculaMock = $this->getCleanMock('clsPmieducarMatricula');
    $matriculaMock->expects($this->any())
                  ->method('detalhe')
                  ->will($this->returnValue($returnMatricula));

    // Registra a inst�ncia no reposit�rio de classes de CoreExt_Entity
    App_Model_IedFinder::addClassToStorage('clsPmieducarMatricula',
      $matriculaMock, NULL, TRUE
    );

    // Mock para RegraAvaliacao_Model_DataMapper
    $mapperMock = $this->getCleanMock('RegraAvaliacao_Model_RegraDataMapper');
    $mapperMock->expects($this->once())
               ->method('find')
               ->with(1)
               ->will($this->returnValue($expected));

    $regraAvaliacao = App_Model_IedFinder::getRegraAvaliacaoPorMatricula(1, $mapperMock);
    $this->assertEquals($expected, $regraAvaliacao);
  }

  /**
   * @depends App_Model_IedFinderTest::testInstanciaRegraDeAvaliacaoPorMatricula
   */
  public function testDisciplinasPorMatricula()
  {
    $componentes = array(
      new ComponenteCurricular_Model_Componente(
        array('id' => 1, 'nome' => 'Matem�tica', 'cargaHoraria' => 100)
      ),
      new ComponenteCurricular_Model_Componente(
        array('id' => 2, 'nome' => 'Portugu�s', 'cargaHoraria' => 100)
      ),
      new ComponenteCurricular_Model_Componente(
        array('id' => 3, 'nome' => 'Ci�ncias', 'cargaHoraria' => 60)
      ),
      new ComponenteCurricular_Model_Componente(
        array('id' => 4, 'nome' => 'F�sica', 'cargaHoraria' => 60)
      )
    );

    $expected = array(
      1 => $componentes[0],
      3 => $componentes[2]
    );

    // Retorna para clsPmieducarEscolaSerieDisciplina
    $returnEscolaSerieDisciplina = array(
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 1, 'carga_horaria' => 80),
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 2, 'carga_horaria' => NULL),
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 3, 'carga_horaria' => NULL),
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 4, 'carga_horaria' => NULL),
    );

    // Mock para clsPmieducarEscolaSerieDisciplina
    $escolaMock = $this->getCleanMock('clsPmieducarEscolaSerieDisciplina');
    $escolaMock->expects($this->any())
               ->method('lista')
               ->will($this->returnValue($returnEscolaSerieDisciplina));

    // Retorna para clsPmieducarDispensaDisciplina
    $returnDispensa = array(
      array('ref_cod_matricula' => 1, 'ref_cod_disciplina' => 2),
      array('ref_cod_matricula' => 1, 'ref_cod_disciplina' => 4),
    );

    // Mock para clsPmieducarDispensaDisciplina
    $dispensaMock = $this->getCleanMock('clsPmieducarDispensaDisciplina');
    $dispensaMock->expects($this->any())
                 ->method('lista')
                 ->with(1, 1, 1)
                 ->will($this->returnValue($returnDispensa));

    // Mock para ComponenteCurricular_Model_ComponenteDataMapper
    $mapperMock = $this->getCleanMock('ComponenteCurricular_Model_ComponenteDataMapper');
    $mapperMock->expects($this->any())
               ->method('findComponenteCurricularAnoEscolar')
               ->will($this->onConsecutiveCalls($expected[1], $expected[3]));

    // Registra mocks
    CoreExt_Entity::addClassToStorage('clsPmieducarEscolaSerieDisciplina',
      $escolaMock, NULL, TRUE);
    CoreExt_Entity::addClassToStorage('clsPmieducarDispensaDisciplina',
      $dispensaMock, NULL, TRUE);

    $disciplinas = App_Model_IedFinder::getComponentesPorMatricula(1, $mapperMock);

    // O esperado � que use a carga hor�ria de escola_serie_disciplina ao
    // inv�s de componente_curricular_ano_escolar.
    // Usa clone para clonar a inst�ncia, sen�o usaria a mesma (copy by reference)
    $expected[1] = clone($expected[1]);
    $expected[1]->cargaHoraria = 80;

    $expected[3] = clone($expected[3]);
    $expected[3]->cargaHoraria = 60;

    $this->assertEquals($expected, $disciplinas);
  }

  /**
   * @depends App_Model_IedFinderTest::testInstanciaRegraDeAvaliacaoPorMatricula
   */
  public function testEtapasDeUmCursoPadraoAnoEscolar()
  {
    $returnEscolaAno = array(
      array('ref_cod_escola' => 1, 'ano' => 2009, 'andamento' => 1, 'ativo' => 1)
    );

    $returnAnoLetivo = array(
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 1),
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 2),
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 3),
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 4)
    );


    // Mock para escola ano letivo (ano letivo em andamento)
    $escolaAnoMock = $this->getCleanMock('clsPmieducarEscolaAnoLetivo');
    $escolaAnoMock->expects($this->any())
                  ->method('lista')
                  ->with(1, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 1)
                  ->will($this->returnValue($returnEscolaAno));

    // Mock para o ano letivo (m�dulos do ano)
    $anoLetivoMock = $this->getCleanMock('clsPmieducarAnoLetivoModulo');
    $anoLetivoMock->expects($this->any())
                  ->method('lista')
                  ->with(2009, 1)
                  ->will($this->returnValue($returnAnoLetivo));

    // Adiciona mocks ao reposit�rio est�tico
    App_Model_IedFinder::addClassToStorage('clsPmieducarEscolaAnoLetivo',
      $escolaAnoMock, NULL, TRUE);
    App_Model_IedFinder::addClassToStorage('clsPmieducarAnoLetivoModulo',
      $anoLetivoMock, NULL, TRUE);

    $etapas = App_Model_IedFinder::getQuantidadeDeEtapasMatricula(1);

    $this->assertEquals(4, $etapas);
  }
}