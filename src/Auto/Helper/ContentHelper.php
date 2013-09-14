<?php
/**
 * ContentHelper class
 * @package    Helper
 * @subpackage ContentHelper
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * ContentHelper class for interactiosn with content that need to be generated to the screen.  Files and strings.
 */
class ContentHelper extends Helper {
    /**
     * @var array Random sentences
     */
    protected $sentence = array(
        'A drift overflows a sequential jazz.',
        'The plastic mutters opposite a sail!',
        'The wish comforts the damp opposite each french aardvark.',
        'Any misplaced shirt barks a tedious weather.',
        'The wrecker spits the characteristic over the drained directory.',
        'The noted sea keeps the alien.',
        'The exercise reads below her playground.',
        'The clock flips against the dog.',
        'The arm persists in a mimic resistance.',
        'A plane nickname bars the silent.',
        'A despair prints the torture next to the starring continental.',
        'A climbing surname crashes throughout the blame.',
        'The remedy flies opposite the chamber!',
        'An idiom jams around a shirt!',
        'Within an appalled champagne groans the patronizing prerequisite.',
        'The ludicrous opinion deletes the champagne.',
        'The wrap boggles next to the glory!',
        'The unattended troop twists the mark.',
        'The held sink bolts.',
        'The beard yawns throughout the slang!',
        'The kidnapped backbone abandons the bat.',
        'The harmony objects before an autumn.',
        'A bay crashes behind a theft!',
        'Every mystery buggers the valued minor.',
        'An advance quarters a chopped professional.',
        'This freeway enforces the endless phoenix.',
        'The page drowns the system.',
        'A theorem revenges the desktop famine without the axiom.',
        'The complaint quits the bias.',
        'The splitting mask counts past the patient.',
        'The hardship shortens the shot fume beside the chemical.',
        'The liquid comprises the sequential eye.',
        'The guilty knee cures the orchestra.',
        'A fat girl pulses in a creed.',
        'The pork withdraws against the peculiar orchestra.',
        'The interim relevance writes after whatever wrecker.',
        'The increased gesture presses against the arena within the damned secret.',
        'A linguistic king works.',
        'The suicide nails the stamped east.',
        'A sweet framework grows.',
        'A ranging pub reflects throughout her kiss.',
        'The sales scream treats the exhausted sword.',
        'The hassle polices an ozone in the cute object.',
        'The pork overprices the unsuccessful desert around the seventh.',
        'The woman works after a riding wine.',
        'The shouted subway cautions within every basis.',
        'Without an eaten earth beams a conceivable boot.',
        'A chaotic overtone scores underneath the family.',
        'The shape facilitates the successive ray.',
        'Behind a complaint interferes the such poem.',
        'The python soaps a plotted wine.',
        'A mature innocence exits outside the air.',
        'A champion focus overlooks the dogma.'
    );
    /**
     * @var array Random paragraphs of sentences
     */
    protected $paragraph = array(
        'The tree explores the crush. The planted stake prosecutes. A notable concert decays against the career. A brigade errs on top of an earlier opus. The passive anagram bicycles within a pretty heritage.',
        'The front dialect cooperates. The relative recovers from the cheer. The skilled vintage refunds a routine. The wet outlook prosecutes. How will a reasoned harden beam over a tone?',
        'Poker trains cards throughout the plane spiritual. Poker weights cards on top of the numeral honey. Cards infers poker without the soup. Poker suspects a discovery. A deaf participates! A bog dies throughout the helpful capital.',
        'The computerized outcome purges below the ambitious representative. The dancing protest lurks after the differential. The apathetic curve invokes the fictional apple. The button covers another paying drill. The helicopter whistles!',
        'The crisp exceeds the through stranger near the brass. The humble demise speculates inside a bass. The crystal plastic writes across the meaning editor. The initiative football escapes a seat.',
        'The torture cruises over a flood league. The saint interferes with a regime. Its ally threads a translator. Will the paragraph surface a frown?',
        'A sordid continent wrongs the salary. The uncle frustrates an inappropriate daughter. The fancy voter overlaps without our tree. A disconnected oil rests before a responsible luxury. The sung pardon does the enterprise near the practicing dogma.',
        'A standpoint rests underneath a substance! The decreased arena thirsts for a nightmare on top of an equivalent microcomputer. The aunt warps the falling blood. A dashing believer poses. The refund pretends below a drained machinery. A paper offends!',
        'A toad disposes his opposed accent. The overlooked tomato trashes the distributed friendship. The starter pops before the spur. An aided dictator cautions around the precedent corpse. Does the contracted stack bundle the representative nest?',
        'The Automobile dogs his graphic worm past the dictionary. The catalog postulates the fizzy era below the wrapper. An idle shorthand stages a railroad. An incentive reigns! Why won not the alarmed caffeine soap a semantic sabotage? An actor kidnaps an executable residence.',
        'The postscript grabs an unstable snag. The lawn dictates a companion. The circulating potential clogs next to the truncate dummy. An unfamiliar abuse flushes the positive yeti.',
        'A wed slave fishes the skull. A returning bubble mixes the convict. A world tears within an accountant. Throughout the frequent girl loses the ludicrous market. The intellectual inhabitant pants. Our lunatic pencils the supermarket within a sound noun.',
        'The patronizing alias balances the charge behind a trivial censorship. The decay leaks! A satellite pig fears within a serious prostitute. An owing boot farms with the motor.',
        'How can the triangle define a supervised deadline? The coal boggles next to a consulting spigot. Why does the deputy doom format my bath? When can the fountain index a nearest chain? A through menu bucks.',
        'The baby forbids the elaborate parrot. The journalist speaks near the supermarket. The comedy fleshes an artificial rip. The boat changes a break. The harmful clash stretches.',
        'How does the lung cry whatever countless leader? Does the override stir a misunderstood analogy? Past an awaited elephant chalks the unexplained critic. Can the founded width pinch a some intellectual? Inside a steer stumbles the rattled shade.',
        'The deaf talks opposite the beforehand confidence. Outside an incompetence prosecutes an empty scope. Can the cider persist? A ruler rattles. The addicted address works underneath the operator. How can the column stir the glow?',
        'Below the animal chews a beard. The realm relaxes into whatever adult insistence. Whatever ignored cartoon knocks a teenage. This limiting physics guts a zero. A motive shines behind the inhabitant! The zero capitalist bounces beneath the bitten choice.',
        'A beard crowds the observer. Any cured cake prefaces the editorial across the fast stress. The damning boot experiments throughout the age. How can the offender speak the expert maker? The undocumented eccentric emphasizes the grandfather throughout a discrete circumstance. A tear paces the flat jazz.',
        'An injustice cruises inside whatever impersonal heart. His rampant skin flames an excellent endeavor. The injury leads a blamed home. The regret waits near a rival. The straight terror violates the leaflet.',
        'Whatever bloody fellow originates. The deserted man slides below the bell. The respected node eliminates the suspicion. Our dialect mends?',
        'Should the hassle stretch under the informative paste? When will Student plant Doctor? Student pencils Doctor outside an ethic. A sinful stray mangles Doctor underneath the objective. Student decays! The fear volunteers throughout the coat.',
        'The finite creature pools a dictatorship next to the worried brother. Japan charges Student underneath the emergency override. Does Japan toll the experienced romance? Will Japan abort beneath Student?',
        'Moodlerooms plays. The terminated badge motivates Student behind a sympathy. Will the controlled worm pulse? The blest sphere lies. Student urges Moodlerooms around the cue. Moodlerooms multiplies under the temporary package.',
        'How does Student rule Moodlerooms? Moodlerooms waffles after a dominated tribe. How can Moodlerooms reason across Student? Moodlerooms listens. Moodlerooms enters! Student condones Moodlerooms.',
        'Can Moodlerooms pop across the percent? The pretended discharge consults Moodlerooms before whatever convict tax. Into Moodlerooms bores any mere immortal. A steady hate ducks underneath Student. Moodlerooms splits Student.',
        'The military bedroom junks the bubble. Student slopes Moodlerooms without a charm. Moodlerooms obsesses Student across the major bargain. Why won not the temple steam? Can Moodlerooms oppose Student?',
        'Teacher spins Moodlerooms. An enemy freezes? The implemented perspective barks Moodlerooms after a bulletin. A result sings. The worthless skull pops the follower.',
        'Teacher adds Moodlerooms. The heel weds a joined pressure without the tragedy. The ashcan exercises outside Teacher. Moodlerooms participates across Teacher. Moodlerooms tips the epic under a developer. A cathedral divorces Teacher.',
        'An army bricks Teacher. Moodlerooms worries across the caffeine. How can Moodlerooms ace the explicit saga? The reject lumps Moodlerooms. Moodlerooms smiles behind an agreeing opponent.',
        'The billfold soils the loaded pulse. Why does a distressing army twin this sensitive drill? The cartoon conceives the agreed bundle against the bitmap. Over a country clogs Moodlerooms. When will the chemical sabotage Moodlerooms?',
        'Moodlerooms bays against the citizen. Why does the opened balance score? A case worries Moodlerooms. Moodlerooms asks a concerto. Should any frustrate deadline core our soap? The singer bites Moodlerooms beneath the appealing choir.',
        'America runs with a medium. America grants the bored family. The insult creams America. The ecology functions as America after a taste. How can America duck throughout the kid?',
        'A peripheral cylinder rolls. The quest pants under America. The sky mirrors America over an erased arrival. A moron walks the reverse initiative beneath the nuclear overview.',
        'Why Could not the lasting classic frequent the flushed psychologist? The patent aborts above America. How does the shared tunnel bow? The flower defects inside our adapted wire. America decays! A hideous symptom defects.',
        'Within the client reasons America. America discriminates! A cheerful devil cheats against the vessel. America grinds the mass. The unpleasant sarcasm moves the shot near the subject grief. Why does the undefined coincidence hold the profound shoulder?',
        'Why Could not America click? America mutters over the rocket. Why Could not a skeleton fear behind America? An astronomy amends America. Every proposed tragedy eases the blame without the robot. America charges each poet throughout a labeled impulse.',
        'Every diagnosis discourages a snack. The bugs hash manipulates America below the hostile hero. The caffeine concatenates a racist over the echo. A disco thirsts. America triumphs past a march. Your visible couples America.',
        'Any work cables the romantic help over a jungle. America wears the unsuspecting decade behind the lay stamp. Does the camp border an eating gold? A sad microprocessor shifts an entire representative.',
        'A justice supplies the hollow trigger after an upsetting disclaimer. Can America degenerate opposite the renewed electorate? America inhabits the method inside the event. A laugh stands a least agony.',
        'The liquid inconveniences America. The schedule associates the graphic competitor near the formed movie. America prides the moon. Will America minimize the rounding prostitute? How does his uniform librarian complain next to America? America uprights the beef.',
        'Student ditches Teacher. Teacher prints Student outside the general voltage. A given garage works. Student adapts our rested debugger. Teacher corns Student.',
        'The preface parades near an abstract! Teacher whistles Student. Teacher fells another catholic. When will the solicitor shine past a widest revenge? A correspondence reasons under the inheritance! Student detaches Teacher.',
        'The overriding razor kids beside Student. When can a shy manner react across Teacher? Student entitles the gateway. Teacher caves Student across the offset. The snack cooperates with every anger! Student complements Teacher beneath the spreading ecology.',
        'An attorney dooms Teacher beneath the crisp dictator. The combat whistles a country. Should an approaching analysis wrong Student? Near Teacher pretends Student.',
        'Beside the trusting diagnosis breaks the unused bandwidth. An optimal basis files Teacher around a desired mercury. Student sells Teacher. Student comprises Teacher. Why does Student pump before Teacher? Does Teacher reject the affair?',
        'Will Teacher quarter Student? Another disturbance weighs her keyword beside the forbidden rival. Can a cleaned process jam into Teacher? The well sounds a headed desert. The even style hopes in Student.',
        'The power volunteers past a musician. Teacher sizes a stressed guide. The floor complains throughout the blob. Student finishes. Student blasts Teacher. Teacher sleeps.',
        'Another seed slips your aggressive excess. Teacher ices the doe under the buss. Teacher flips beneath Student. Why Could not an alleged horde analyze a window? A link spins Teacher throughout a temper.',
        'Student plates a leaded sin below a conceived ratio. The daily brain remedies Student. Teacher deserts Student below the afternoon terrorist. The syndrome wishes the utter circuit within the gown. Teacher triumphs behind a console.',
        'After Student conforms the pedestrian. Student offsets another purpose. The horse pops Student behind another profound birth. Under Teacher intervenes the playground.'
    );
    /**
     * @var array Random questions
     */
    protected $question = array(
        'Will the dish pull the usual blank?',
        'Should the inertia issue from the select ego?',
        'How will the improving carrier nickname a resolve?',
        'Does a photograph dance the native conference?',
        'Does the granted unknown fork a handbook?',
        'Will the awkward fuse flash the genius?',
        'Does the attractive journalist believe the moon?',
        'When will the striking electron arrive next to a motor?',
        'How will the king balance a ruling nest?',
        'Why won not a mixture mob the dive?',
        'Should the oldest tome defect?',
        'How does John Wayne do it?',
        'When will Moodle 2.0 be released?',
        'Who has control of my information?',
        'How do I dance the tango?',
        'Can birds fly backwards?',
        'Do cars have manifolds?',
        'When is the semester over?',
        'Can chickens eat cat food?',
        'What is a marmaduke?',
        'Is Moodlerooms a SAS company?',
        'Do we have a councilor on campus?',
        'What era was Casablanca in?',
        'Do films have silver in them anymore?',
        'What is the impact of streaming video on film?',
        'Is flash an appropriate video type for web?',
        'Does the iPad support flash?',
        'Can toast be burnt?',
        'When a camel spits is it gross?',
        'Do dogs bark at squirrels?',
        'Is there a banana in my ear?',
        'What is your favorite video?',
        'Can you dance a jig?',
        'How do computers compute?',
        'When is too much too little?',
        'Where is my mustache?',
        'Are drinks free or do they cost?',
        'Can you dance to mariachi?'
    );

    /**
     * @var array Random headings
     */
    protected $header = array(
        'A drift overflows a sequential jazz.',
        'The plastic mutters opposite a sail!',
        'The wish comforts the damp opposite each french aardvark.',
        'Any misplaced shirt barks a tedious weather.',
        'The wrecker spits the characteristic over the drained directory.',
        'The noted sea keeps the alien.',
        'The exercise reads below her playground.',
        'The clock flips against the dog.',
        'The arm persists in a mimic resistance.',
        'A plane nickname bars the silent.',
        'A despair prints the torture next to the starring continental.',
        'A climbing surname crashes throughout the blame.',
        'The remedy flies opposite the chamber!',
        'An idiom jams around a shirt!',
        'Within an appalled champagne groans the patronizing prerequisite.',
        'The ludicrous opinion deletes the champagne.',
        'The wrap boggles next to the glory!',
        'The unattended troop twists the mark.',
        'The held sink bolts.',
        'The beard yawns throughout the slang!',
        'The kidnapped backbone abandons the bat.',
        'The harmony objects before an autumn.',
        'A bay crashes behind a theft!',
        'Every mystery buggers the valued minor.',
        'An advance quarters a chopped professional.',
        'This freeway enforces the endless phoenix.',
        'The page drowns the system.',
        'A theorem revenges the desktop famine without the axiom.',
        'The complaint quits the bias.',
        'The splitting mask counts past the patient.',
        'The hardship shortens the shot fume beside the chemical.',
        'The liquid comprises the sequential eye.',
        'The guilty knee cures the orchestra.',
        'A fat girl pulses in a creed.',
        'The pork withdraws against the peculiar orchestra.',
        'The interim relevance writes after whatever wrecker.',
        'The increased gesture presses against the arena within the damned secret.',
        'A linguistic king works.',
        'The suicide nails the stamped east.',
        'A sweet framework grows.',
        'A ranging pub reflects throughout her kiss.',
        'The sales scream treats the exhausted sword.',
        'The hassle polices an ozone in the cute object.',
        'The pork overprices the unsuccessful desert around the seventh.',
        'The woman works after a riding wine.',
        'The shouted subway cautions within every basis.',
        'Without an eaten earth beams a conceivable boot.',
        'A chaotic overtone scores underneath the family.',
        'The shape facilitates the successive ray.',
        'Behind a complaint interferes the such poem.',
        'The python soaps a plotted wine.',
        'A mature innocence exits outside the air.',
        'A champion focus overlooks the dogma.'
    );
    /**
     * @var array Random words
     */
    protected $word = array(
        'albatross',
        'army',
        'ariel',
        'accident',
        'alphabet',
        'acronym',
        'bankroll',
        'bell',
        'bat',
        'bingo',
        'bongo',
        'banister',
        'bug',
        'broom',
        'car',
        'can',
        'cup',
        'clam',
        'course',
        'danger',
        'doom',
        'doctor',
        'delicious',
        'eldridge',
        'elder',
        'east',
        'fork',
        'fake',
        'flat',
        'forum',
        'focus',
        'fit',
        'fight',
        'fat',
        'folk',
        'foot',
        'false',
        'grave',
        'gallant',
        'gentry',
        'grove',
        'gut',
        'gross',
        'horse',
        'hot',
        'hat',
        'hollow',
        'hill',
        'hair',
        'honey',
        'igloo',
        'icky',
        'jump',
        'jury',
        'jackel',
        'jingle',
        'kite',
        'kitchen',
        'ketchup',
        'luck',
        'lantern',
        'lecture',
        'learn',
        'lock',
        'mouse',
        'moose',
        'moron',
        'might',
        'mammal',
        'minimize',
        'noun',
        'nut',
        'nose',
        'night',
        'normal',
        'naughty',
        'open',
        'occasion',
        'occult',
        'octave',
        'olive',
        'original',
        'porus',
        'pour',
        'pickle',
        'pocket',
        'pool',
        'pale',
        'patty',
        'potty',
        'pepper',
        'quick',
        'quiet',
        'quirk',
        'row',
        'rock',
        'roll',
        'root',
        'racket',
        'rain',
        'rookie',
        'serpent',
        'safe',
        'salt',
        'sizzle',
        'slurp',
        'slap',
        'slick',
        'sick',
        'sort',
        'torte',
        'tempt',
        'target',
        'talent',
        'tickle',
        'tackle',
        'true',
        'torque',
        'universal',
        'unique',
        'utility',
        'useless',
        'violent',
        'visual',
        'vocal',
        'verbal',
        'version',
        'worry',
        'window',
        'warp',
        'wrap',
        'wacky',
        'xenophobe',
        'xray',
        'xeroma',
        'zip',
        'zap'
    );
    /**
     * @var array Set of first names, mix of male and female names
     */
    protected $firstnames = array(
        'bob',
        'brandi',
        'betty',
        'bobbie',
        'brian',
        'jake',
        'mark',
        'michael',
        'mike',
        'jack',
        'john',
        'tim',
        'saul',
        'sarah',
        'phil',
        'tom',
        'sam',
        'donna',
        'chris',
        'miguel',
        'tami',
        'cai',
        'colin',
        'jill',
        'jessica',
        'laura',
        'michelle',
        'aaron',
        'daniel',
        'kathryn',
        'christine',
        'christian',
        'lou',
        'andy',
        'jessie',
        'meghan',
        'morgan',
        'tiffany',
        'susan',
        'cassie',
        'candie',
        'carrie',
        'cory',
        'clyde',
        'po',
        'xing',
        'ming',
        'daphnie',
        'susan',
        'carl',
        'phill',
        'ralph',
        'derik'
    );
    /**
     * @var array Mix of last names, attempts to have multiple countries of origin
     */
    protected $lastnames = array(
        'smith',
        'gonzales',
        'jones',
        'takawa',
        'johnson',
        'wong',
        'long',
        'williams',
        'brown',
        'davis',
        'russel',
        'griffin',
        'ford',
        'hamilton',
        'cole',
        'west',
        'owens',
        'fisher',
        'rynolds',
        'jenkins',
        'foster',
        'woods',
        'long',
        'washington',
        'butler',
        'ross',
        'myers',
        'graham',
        'cruz',
        'james',
        'ramirez',
        'brooks',
        'cox',
        'price',
        'nielsen',
        'parker',
        'lu',
        'takawa',
        'miller',
        'mercer',
        'vargez',
        'lopez',
        'alverez',
        'stevens',
        'akutagawa',
        'xu',
        'weston',
        'weaton',
        'mcAlister',
        'mcDonald',
        'o\'Neil',
        'o\'Connell'
    );

    protected $teacherComment = array(
        'Your work showed a lot of promise keep it up',
        'Your work needs improvement, please see me during my office hours',
        'Please provide more detail in your work. I am looking for a better explination',
        'This was great effort keep up the good work',
        'Please review the materials for this week and resubmit the assignment',
        'I need more from you at this time.'
    );
    /**
     * @var array HTML attributes that are associated with each type of information array for when the information is returned as HTML
     */
    protected $html = array(
        'paragraph' => 'p',
        'sentence'  => 'p',
        'question'  => 'p',
        'header'    => 'h2',
        'teacherComment'    => 'p',
        'word'    => 'p'
    );
    /**
     * @var array Creole Moodle wiki wrappers for each type of content arrays that are returned.
     */
    protected $containerreole = array(
        'paragraph' => '\\\\',
        'sentence'  => '\\\\',
        'question'  => '\\\\',
        'header'    => '='
    );

    /**
     * Setup the helper because construct is called with no variables early on in the Application processing and configuration
     *
     * @param \Auto\Container $c configured container to access logs and mink script
     */
    public function setUp($c) {
        $this->container = $c;
    }

    /**
     * Grab a Random person's name
     *
     * @param string $type f for first name and l for last name
     *
     * @return string the name
     */
    public function getPersonName($type = 'f') {
        $names = array('f' => $this->firstnames, 'l' => $this->lastnames);

        $rand = rand(0, count($names[$type]) - 1);

        return ucfirst($names[$type][$rand]);
    }

    /**
     * Grab a specific first or last name by the id in the array
     *
     * @param        $id   Array id for the first or last name to return
     * @param string $type f for first name and l for last name
     *
     * @return string The name
     */
    public function getNameByID($id, $type = 'f') {
        $names = array('f' => $this->firstnames, 'l' => $this->lastnames);

        return ucfirst($names[$type][$id]);
    }

    /**
     * Generate a random essay
     *
     * @param string $output plaintext or html for the return type
     *
     * @return string HTML or plaintext essay
     */
    public function getRandEssay($output = 'plaintext') {
        return $this->getRandHeader($output) . $this->getRandParagraph($output) . $this->getRandParagraph($output) . $this->getRandParagraph($output) . $this->getRandParagraph($output);
    }

    /**
     * Generate a random paragraph of data
     *
     * @param string $output plaintext, html or creole  the return type
     *
     * @return string
     */
    public function getRandParagraph($output = 'plaintext') {
        return $this->getRandText('paragraph', $output);
    }

    /**
     * Generate a random question
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandQuestion($output = 'plaintext') {
        return $this->getRandText('question', $output);
    }

    /**
     * Generate a random header
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandHeader($output = 'plaintext') {
        return $this->getRandText('header', $output);
    }

    /**
     * Generate a random sentence
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandSentence($output = 'plaintext') {
        return $this->getRandText('sentence', $output);
    }

    /**
     * Generate a random word
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandWord($output = 'plaintext') {
        return $this->getRandText('word', $output);
    }

    public function getRandTeacherComment($output = 'plaintext'){
        return $this->getRandText('teacherComment', $output);
    }

    /**
     * Create random text from the arrays
     *
     * @param $type   The type of text to create word, sentence, header, paragraph, or question
     * @param $output one of three types plaintext, html or creole for the Moodle wiki.
     *
     * @return string
     */
    public function getRandText($type, $output) {
        $randnum = rand(0, (count($this->{$type}) - 1));

        $text = $this->{$type}[$randnum];

        if ($output == 'html') {
            $tag  = $this->html[$type];
            $text = "<$tag>$text</$tag>";
        } else if ($output == 'creole') {
            $tag  = $this->html[$type];
            $text = "$tag $text $tag ";
        }

        return $text;
    }

    /**
     * Create a wiki body post in creole
     * @return string Wiki post
     */
    public function getWikiBody() {
        $wikibody = $this->getRandHeader('creole') . $this->getRandParagraph('creole');

        foreach ($this->header as $header) {
            $wikibody .= "[[$header]]\\\\";
        }

        return $wikibody;
    }

    /**
     * Return a random file of the extention in the directory and type provided. Intended for the included files directory
     *
     * @param $dir  The base directory where the files are stored, usually $this->container->logHelper->fdir
     * @param $type The type of file to be getting, math, english, scorm, imscp
     * @param $ext  The file's extension also a directory, pdf, zip, docx
     *
     * @return string|bool
     */
    public function getRandFile($dir, $type, $ext) {
        $filedir = $dir . $type . '/' . $ext . '/';
        if (is_dir($filedir)) {
            $files  = scandir($filedir, 1);
            $picked = rand(0, (count($files) - 3));
            return $filedir . $files[$picked];
        } else {
            return false;
        }
    }

    /**
     * Create a random name to save the file as in the repository browser
     * @return string Random file name
     */
    public function getFilename() {
        return '-rev' . rand(2, 9999999999);
    }

    /**
     * Grab a file from the sent directory and then upload it to Moodle via the upload file repository
     *
     * @param        $dir    The base directory where the files are stored, usually $this->container->logHelper->fdir
     * @param        $type   The type of file to be getting, math, english, scorm, imscp
     * @param        $ext    The file's extension also a directory, pdf, zip, docx
     * @param string $saveas The name to save the file as
     */
    public function uploadRandFile($dir, $type, $ext, $saveas = '') {
        if($filename = $this->getRandFile($dir, $type, $ext)){
            $this->addFile($filename, $saveas);
        }
    }

    /**
     * Click on the add file link in the file browser or the choose a file button for scorm and imscp. Then execute the file upload repository.
     * TODO: Add support for other Moodle repostiories like server files, or recent files.
     *
     * @param        $file   The full path to the file to be uploaded
     * @param string $saveas the name of the file to be saved as
     */
    public function addFile($file, $saveas = '') {
        if (file_exists($file)) {
            $element = 0;
            $this->container->reloadPage();
            if ($div = $this->container->page->find('css', '.fp-btn-add')) {
                sleep($this->container->cfg->delay); // it seems some javascript is running to update the file manager.  The add button can be found and then hidden when the .fm-maxfiles class is applied.
                /// There is a class added to hide the add button when the maximum allowed files is reached.
                if ($max = $this->container->page->find('css', '.filemanager.fm-maxfiles')) {
                    $this->container->logHelper->action('Maximum files have been uploaded');
                    $element = 0;
                } else {
                    $this->container->logHelper->action('Found the fp-btn-add button');
                    $element = $div->find('css', 'a');
                }
            } else if ($element = $this->container->page->findButton('Choose a file...')) {
                sleep($this->container->cfg->delay);
                $this->container->logHelper->action('Found the Choose a file... button');
            }
            if (!empty($element)) {
                $element->click();
                if ($repoarea = $this->container->page->find('css', '.fp-list')) {
                    if ($uploadrepo = $repoarea->findLink('Upload a file')) {
                        $uploadrepo->click();
                    /// Need to delay looking for AJAX to process and part of the page to be unhidden or added.
                        sleep($this->container->cfg->delay);
                        if ($upload = $this->container->page->findField('repo_upload_file')) {
                            $this->container->logHelper->action('Attaching file ' . $file . ' in upload repository');
                            $upload->attachFile($file);

                            if (!empty($saveas)) {
                                $element = $this->container->page->findField('title');
                                $element->setValue($saveas);
                            }
                            $button = $this->container->page->findButton('Upload this file');
                            try {
                                $button->press();
                            } catch (Exception $e) {
                                //do nothing because the likely issue is an alert that we can't handle.
                            }
                        } else {
                            $this->container->logHelper->action('Could not find .fp-upload-form in repository browser');
                        }
                    } else {
                        $this->container->logHelper->action('Could not find the upload a file link in repository browser');
                    }
                } else {
                    $this->container->logHelper->action('Could not find .fp-list in repository browser');
                }
            } else {
                $this->container->logHelper->action('Could not find the filepicker add button or choose a file button');
            }
        } else {
            $this->container->logHelper->action($file . ' Does not exist on the computer');
        }
    }

    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    public function getName() {
        return 'content';
    }
}

?>