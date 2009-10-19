<?php
/**
  * DL-Learner solution table
  *
  * @author Sebastian Dietzold
  * @version $Id$
  */

?>
    <fieldset><legend><?php echo $this->legend ?></legend>
    <table cellspacing="0" class="separated-vertical">
        <thead><tr>
            <th />
            <th><?php echo $this->thAccuracy ?></th>
            <th><a class="dll-showAllDetails minibutton">toggle&nbsp;all</a></th>
            <th class="width99"><?php echo $this->thExpression ?></th>
        </tr></thead>
        <tbody>
<?php $odd = true; ?>
<?php foreach ($this->solutions as $solution ): ?>
        <tr class="dll-solution <?php echo $odd ? 'odd' : 'even'; $odd = !$odd; ?>">
            <td class="selector"><input type='radio' value='<?php echo $solution['preparedDescription'] ?>' name='solution' /></td>
            <td><?php echo $solution['accuracy'] ?>%</td>
            <td><a class="dll-showDetails minibutton">toggle&nbsp;details</a></td>
            <td>
                <div>
                <?php
                    echo $solution['preparedDescription'];
                    if ($solution['isConsistent'] != 1) { echo '&nbsp;<span class="dll-inconsistent">inconsistent</span>'; }
                ?>
                </div>
                <div class="dll-details" style="display: none">
                    <img src="<?php echo $solution['chartImg'] ?>"
                         style="float: right; height: 8em; margin-left: 0.5em" />
                <ul class="separated-vertical bullets-disc">
                    <?php if ($solution['titledCoveredInstances']) :?>
                    <li><strong>Covered Instances (<?php echo $solution['coverage'] ?>%):</strong>
                        <ol class="bullets-none separated-horizontal" >
                        <?php foreach ($solution['titledCoveredInstances'] as $uri => $instance): ?>
                            <li><a  class="Resource hasMenu"
                                    about="<?php echo $uri ?>"
                                    href="<?php echo $instance['link'] ?>"
                                   ><?php echo $instance['title'] ?></a></li>
                        <?php endforeach; ?>
                        </ol></li>
                    <?php endif; ?>
                    <?php if ($solution['titledAdditionalInstances']) :?>
                        <li><strong>Additional Instances:</strong>
                            <ol class="bullets-none separated-horizontal" >
                        <?php foreach ($solution['titledAdditionalInstances'] as $uri => $instance): ?>
                            <li><a  class="Resource hasMenu"
                                    about="<?php echo $uri ?>"
                                    href="<?php echo $instance['link'] ?>"
                                   ><?php echo $instance['title'] ?></a></li>
                        <?php endforeach; ?>
                        </ol></li>
                    <?php endif; ?>
                </ul>
                </div>
            </td>
        </tr>
<?php endforeach; ?>
        </tbody></table>
</fieldset>

<div class="messagebox info">
    <img alt="logo" src="http://aksw.org/Projects/DLLearner/files?get=dllearner.gif" style="float:right" />
   The suggestions were generated by <a href="http://dl-learner.org">DL-Learner</a>. For more information about the DL-Learner plugin, see the <a href="http://dl-learner.org/wiki/OntoWikiPlugin">OntoWiki DL-Learner Plugin Page</a>.
</div>
