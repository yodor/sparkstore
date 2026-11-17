<?php
include_once("components/Component.php");
include_once("components/Action.php");
include_once("utils/IGETConsumer.php");

/**
 * Letter links ?letter=<letter> of the current address - one row per alphabet; the selected letter has selected='1'.
 * Markup: div.AlphabetChooser > div.items > a.Action
 */
class AlphabetChooser extends Component implements IGETConsumer
{
    /**
     * The letters, one row each
     * @var array<string>
     */
    public array $alphabets = array("АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЮЯ", "ABCDEFGHIJKLMNOPQRSTUVWXYZ");

    protected Action $action;

    protected string $selected_letter = "";

    public function __construct()
    {
        parent::__construct();
        $this->action = new Action();

        $url = $this->action->getURL();
        $url->setClearPageParams(true);
        $url->add(new DataParameter("letter"));

        $letter = (string)($_GET["letter"] ?? "");
        $this->selected_letter = (mb_strlen($letter) > 0) ? mb_substr($letter, 0, 1) : "";
    }

    public function getURL() : URL
    {
        return $this->action->getURL();
    }

    public function getSelectedLetter() : string
    {
        return $this->selected_letter;
    }

    protected function renderImpl(): void
    {
        parent::renderImpl();
        foreach ($this->alphabets as $alphabet) {
            $this->renderAlphabet($alphabet);
        }
    }

    protected function renderAlphabet(string $alphabet) : void
    {
        echo "<div class='items'>";
        for ($a = 0; $a < mb_strlen($alphabet); $a++) {
            $letter = mb_substr($alphabet, $a, 1);
            $this->action->removeAttribute("selected");
            if (strcmp($letter, $this->selected_letter) === 0) {
                $this->action->setAttribute("selected", "1");
            }
            $this->action->setData(array("letter" => $letter));
            $this->action->setText($letter);
            $this->action->render();
        }
        echo "</div>";
    }

    /**
     * @return array The parameter names this object is interacting with
     */
    public function getParameterNames(): array
    {
        return array("letter");
    }
}
