<?php

namespace TwoColConflict\Tests;

use MediaWiki\Content\Content;
use MediaWiki\Revision\RevisionRecord;
use MediaWikiUnitTestCase;
use TwoColConflict\TalkPageConflict\ResolutionSuggester;
use TwoColConflict\TalkPageConflict\TalkPageResolution;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TwoColConflict\TalkPageConflict\ResolutionSuggester
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class ResolutionSuggesterTest extends MediaWikiUnitTestCase {

	public static function provideSuggestion() {
		return [
			[
				'base' => '',
				'your' => 'B',
				'stored' => 'C',
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">C</ins>',
							'newtext' => 'C',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">B</ins>',
							'newtext' => 'B',
						],
					],
					0,
					1
				),
			],
			[
				'base' => "",
				'your' => "B\nB",
				'stored' => "C\nC",
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">C\nC</ins>",
							'newtext' => "C\nC",
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">B\nB</ins>",
							'newtext' => "B\nB",
						],
					],
					0,
					1
				),
			],
			[
				'base' => "A",
				'your' => "A\nB",
				'stored' => "A\nC",
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'copy',
							'copytext' => 'A',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">C</ins>",
							'newtext' => 'C',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">B</ins>",
							'newtext' => 'B',
						],
					],
					1,
					2
				),
			],
			[
				'base' => "A\n\nA",
				'your' => "A\n\nA\nB",
				'stored' => "A\n\nA\nC",
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'copy',
							'copytext' => "A\n\nA",
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">C</ins>",
							'newtext' => 'C',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">B</ins>",
							'newtext' => 'B',
						],
					],
					1,
					2
				),
			],
			[
				'base' => "A",
				'your' => "A\nB\nB",
				'stored' => "A\nC",
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'copy',
							'copytext' => 'A',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">C</ins>",
							'newtext' => 'C',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">B\nB</ins>",
							'newtext' => "B\nB",
						],
					],
					1,
					2
				),
			],
			[
				'base' => "A",
				'your' => "B\nA",
				'stored' => "C\nA",
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">C</ins>",
							'newtext' => 'C',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">B</ins>",
							'newtext' => 'B',
						],
						[
							'action' => 'copy',
							'copytext' => 'A',
						],
					],
					0,
					1
				)
			],
			[
				'base' => "A\nA",
				'your' => "A\nB\nA",
				'stored' => "A\nC\nA",
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'copy',
							'copytext' => 'A',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">C</ins>",
							'newtext' => 'C',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">B</ins>",
							'newtext' => 'B',
						],
						[
							'action' => 'copy',
							'copytext' => 'A',
						],
					],
					1,
					2
				)
			],
			'bug T248668' => [
				'base' => "Initial comment.\n\nLater comment.",
				'your' => "Initial comment.\n:Conflicting response.\n\nLater comment.",
				'stored' => "Initial comment.\n:Inline response.\n\nLater comment.",
				'expected' => new TalkPageResolution(
					[
						[
							'action' => 'copy',
							'copytext' => 'Initial comment.',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">:Inline response.</ins>",
							'newtext' => ':Inline response.',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => "<ins class=\"mw-twocolconflict-diffchange\">:Conflicting response.</ins>",
							'newtext' => ':Conflicting response.',
						],
						[
							'action' => 'copy',
							'copytext' => "\nLater comment.",
						],
					],
					1,
					2
				)
			],
			'bug T251251, change on the left, extra newlines at the bottom' => [
				'base' => "Initial comment.\n\nLater comment.",
				'your' => "Initial comment.\n:Conflicting response.\n\nLater comment.",
				'stored' => "Initial comment.\n:Inline response.\nLater comment.",
				'expected' => new TalkPageResolution(
					[
						[ 'action' => 'copy', 'copytext' => 'Initial comment.' ],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Inline response.</ins>',
							'newtext' => ':Inline response.',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Conflicting response.</ins>',
							'newtext' => ":Conflicting response.\n",
						],
						[ 'action' => 'copy', 'copytext' => 'Later comment.' ],
					],
					1,
					2
				)
			],
			'bug T251251, change on the right, extra newlines at the bottom' => [
				'base' => "Initial comment.\n\nLater comment.",
				'your' => "Initial comment.\n:Conflicting response.\nLater comment.",
				'stored' => "Initial comment.\n:Inline response.\n\nLater comment.",
				'expected' => new TalkPageResolution(
					[
						[ 'action' => 'copy', 'copytext' => 'Initial comment.' ],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Inline response.</ins>',
							'newtext' => ":Inline response.\n",
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Conflicting response.</ins>',
							'newtext' => ':Conflicting response.',
						],
						[ 'action' => 'copy', 'copytext' => 'Later comment.' ],
					],
					1,
					2
				)
			],
			'bug T251251, change on the left, extra newlines at the top' => [
				'base' => "Initial comment.\n\nLater comment.",
				'your' => "Initial comment.\n\n:Conflicting response.\nLater comment.",
				'stored' => "Initial comment.\n:Inline response.\nLater comment.",
				'expected' => new TalkPageResolution(
					[
						[ 'action' => 'copy', 'copytext' => 'Initial comment.' ],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Inline response.</ins>',
							'newtext' => ':Inline response.',
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Conflicting response.</ins>',
							'newtext' => "\n:Conflicting response.",
						],
						[ 'action' => 'copy', 'copytext' => 'Later comment.' ],
					],
					1,
					2
				)
			],
			'bug T251251, change on the right, extra newlines at the top' => [
				'base' => "Initial comment.\n\nLater comment.",
				'your' => "Initial comment.\n:Conflicting response.\nLater comment.",
				'stored' => "Initial comment.\n\n:Inline response.\nLater comment.",
				'expected' => new TalkPageResolution(
					[
						[ 'action' => 'copy', 'copytext' => 'Initial comment.' ],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Inline response.</ins>',
							'newtext' => "\n:Inline response.",
						],
						[
							'action' => 'add',
							'oldhtml' => "\u{00A0}",
							'oldtext' => '',
							'newhtml' => '<ins class="mw-twocolconflict-diffchange">:Conflicting response.</ins>',
							'newtext' => ':Conflicting response.',
						],
						[ 'action' => 'copy', 'copytext' => 'Later comment.' ],
					],
					1,
					2
				)
			],
			[
				'base' => "A\nA",
				'your' => "A\nB\nA",
				'stored' => "A\nA\nC",
				'expected' => null,
			],
			[
				'base' => "A\nA",
				'your' => "B\nB\nA",
				'stored' => "A\nC\nA",
				'expected' => null,
			],
			[
				'base' => "A\nA",
				'your' => "A\nB",
				'stored' => "A\nC\nA",
				'expected' => null,
			],
			[
				'base' => "A",
				'your' => "A\nB",
				'stored' => "C\nC",
				'expected' => null,
			],
			[
				'base' => "A\nA",
				'your' => "A\nB\nA\nD",
				'stored' => "A\nC\nA",
				'expected' => null,
			],
			[
				'base' => "A\nA\nA",
				'your' => "A\nA\nD",
				'stored' => "A\nC\nA",
				'expected' => null,
			],
			'incompatible 3-row diff' => [
				'base' => "A\nB\nC",
				'your' => "1\nB\n1",
				'stored' => "2\nB\n2",
				'expected' => null,
			],
		];
	}

	/**
	 * @dataProvider provideSuggestion
	 */
	public function testSuggestion(
		string $base,
		string $your,
		string $stored,
		?TalkPageResolution $expectedOutput
	) {
		$suggester = $this->createResolutionSuggester( $base );

		$this->assertEquals(
			$expectedOutput,
			$suggester->getResolutionSuggestion(
				explode( "\n", $stored ),
				explode( "\n", $your )
			)
		);
	}

	public function testGetBaseRevisionLines() {
		$suggester = $this->createResolutionSuggester( "A\nB\nC" );
		$this->assertSame( [ 'A', 'B', 'C' ], $suggester->getBaseRevisionLines() );
	}

	public function testGetBaseRevisionLinesNoContent() {
		$suggester = $this->createResolutionSuggester( null );
		$this->assertSame( [], $suggester->getBaseRevisionLines() );
	}

	public function testGetBaseRevisionLinesNoBaseRevision() {
		/** @var ResolutionSuggester $suggester */
		$suggester = TestingAccessWrapper::newFromObject(
			new ResolutionSuggester( null, '' )
		);
		$this->assertSame( [], $suggester->getBaseRevisionLines() );
	}

	/**
	 * @param string|null $wikiText
	 *
	 * @return ResolutionSuggester
	 */
	private function createResolutionSuggester( ?string $wikiText ) {
		$content = $this->createMock( Content::class );
		$content->method( 'serialize' )->willReturn( $wikiText );

		$baseRevisionMock = $this->createMock( RevisionRecord::class );
		$baseRevisionMock->method( 'getContent' )
			->willReturn( $content );
		return TestingAccessWrapper::newFromObject(
			new ResolutionSuggester( $baseRevisionMock, CONTENT_FORMAT_WIKITEXT )
		);
	}

}
