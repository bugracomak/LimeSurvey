<?php
class TextArrayQuestion extends ArrayQuestion
{
    public function getAnswerHTML()
    {
        global $thissurvey;
        global $notanswered;
        $repeatheadings = Yii::app()->getConfig("repeatheadings");
        $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
        $extraclass ="";
        $clang = Yii::app()->lang;

        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "text-keypad";
        }
        else
        {
            $kpclass = "";
        }

        $checkconditionFunction = "checkconditions";
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];

        $defaultvaluescript = "";
        $qquery = "SELECT other FROM {{questions}} WHERE qid={$this->id} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";

        $qresult = Yii::app()->db->createCommand($qquery)->query();
        $qrow = $qresult->read(); $other = $qrow['other'];

        $aQuestionAttributes = $this->getAttributeValues();

        $show_grand = $aQuestionAttributes['show_grand_total'];
        $totals_class = '';
        $num_class = '';
        $show_totals = '';
        $col_total = '';
        $row_total = '';
        $total_col = '';
        $col_head = '';
        $row_head = '';
        $grand_total = '';
        $q_table_id = '';
        $q_table_id_HTML = '';
        $numbersonly = '';

        if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
            $maxlength= "maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= "";
        }
        if ($aQuestionAttributes['numbers_only']==1)
        {
            $checkconditionFunction = "fixnum_checkconditions";
            $q_table_id = 'totals_'.$this->id;
            $q_table_id_HTML = ' id="'.$q_table_id.'"';
            //	$numbersonly = 'onkeypress="return goodchars(event,\'-0123456789.\')"';
            $num_class = ' numbers-only';
            $extraclass.=" numberonly";
            switch ($aQuestionAttributes['show_totals'])
            {
                case 'R':
                    $totals_class = $show_totals = 'row';
                    $row_total = '<td class="total information-item">
                    <label>
                    <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                    </label>
                    </td>';
                    $col_head = '			<th class="total">Total</th>';
                    if($show_grand == true)
                    {
                        $row_head = '
                        <th class="answertext total">Grand total</th>';
                        $col_total = '
                        <td>&nbsp;</td>';
                        $grand_total = '
                        <td class="total grand information-item">
                        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                        </td>';
                    };
                    break;
                case 'C':
                    $totals_class = $show_totals = 'col';
                    $col_total = '
                    <td class="total information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                    $row_head = '
                    <th class="answertext total">Total</th>';
                    if($show_grand == true)
                    {
                        $row_total = '
                        <td class="total information-item">&nbsp;</td>';
                        $col_head = '			<th class="total">Grand Total</th>';
                        $grand_total = '
                        <td class="total grand">
                        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                        </td>';
                    };
                    break;
                case 'B':
                    $totals_class = $show_totals = 'both';
                    $row_total = '			<td class="total information-item">
                    <label>
                    <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                    </label>
                    </td>';
                    $col_total = '
                    <td  class="total information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                    $col_head = '			<th class="total">Total</th>';
                    $row_head = '
                    <th class="answertext">Total</th>';
                    if($show_grand == true)
                    {
                        $grand_total = '
                        <td class="total grand information-item">
                        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled"/>
                        </td>';
                    }
                    else
                    {
                        $grand_total = '
                        <td>&nbsp;</td>';
                    };
                    break;
            };
            if(!empty($totals_class))
            {
                $totals_class = ' show-totals '.$totals_class;
                if($aQuestionAttributes['show_grand_total'])
                {
                    $totals_class .= ' grand';
                    $show_grand = true;
                };
            };
        }
        else
        {
            $numbersonly = '';
        };
        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        }
        else
        {
            $answerwidth=20;
        };
        if (trim($aQuestionAttributes['text_input_width'])!='')
        {
            $inputwidth=$aQuestionAttributes['text_input_width'];
            $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
        }
        else
        {
            $inputwidth = 20;
        }
        $columnswidth=100-($answerwidth*2);

        $lquery = "SELECT * FROM {{questions}} WHERE parent_qid={$this->id}  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=1 ORDER BY question_order";
        $lresult = Yii::app()->db->createCommand($lquery)->query();
        if (count($lresult)> 0)
        {
            foreach($lresult->readAll() as $lrow)
            {
                $labelans[]=$lrow['question'];
                $labelcode[]=$lrow['title'];
            }
            $numrows=count($labelans);
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
            if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
            {
                ++$numrows;
            };
            $cellwidth=$columnswidth/$numrows;

            $cellwidth=sprintf('%02d', $cellwidth);

            $ansquery = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$this->id} and scale_id=0 AND question like '%|%'";
            $ansresult = reset(dbExecuteAssoc($ansquery)->read());
            if ($ansresult>0)
            {
                $right_exists=true;
                $answerwidth=$answerwidth/2;
            }
            else
            {
                $right_exists=false;
            }
            // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
            $ansresult = $this->getChildren();
            $anscount = count($ansresult);
            $fn=1;

            $answer_cols = "\t<colgroup class=\"col-responses\">\n"
            ."\n\t\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";

            $answer_head = "\n\t<thead>\n"
            . "\t\t<tr>\n"
            . "\t\t\t<td width='$answerwidth%'>&nbsp;</td>\n";

            $odd_even = '';
            foreach ($labelans as $ld)
            {
                $answer_head .= "\t<th class=\"answertext\">".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            if ($right_exists)
            {
                $answer_head .= "\t<td>&nbsp;</td>\n";// class=\"answertextright\"
                $odd_even = alternation($odd_even);
                $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$cellwidth%\" />\n";
            }

            if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
            {
                $answer_head .= $col_head;
                $odd_even = alternation($odd_even);
                $answer_cols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            $answer_cols .= "\t</colgroup>\n";

            $answer_head .= "</tr>\n"
            . "\t</thead>\n";

            $answer = "\n<table$q_table_id_HTML class=\"question subquestions-list questions-list{$extraclass}$num_class"."$totals_class\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - an array of text responses\">\n" . $answer_cols . $answer_head;
            $answer .= "<tbody>";
            $trbc = '';
            foreach ($ansresult as $ansrow)
            {
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
                {
                    if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                    {
                        $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                        $answer .= "<tr class=\"repeat headings\">\n"
                        . "\t<td>&nbsp;</td>\n";
                        foreach ($labelans as $ld)
                        {
                            $answer .= "\t<th>".$ld."</th>\n";
                        }
                        $answer .= "</tr>\n";
                    }
                }
                $myfname = $this->fieldname.$ansrow['title'];
                $answertext = dTexts__run($ansrow['question']);
                $answertextsave=$answertext;
                /* Check if this item has not been answered: the 'notanswered' variable must be an array,
                containing a list of unanswered questions, the current question must be in the array,
                and there must be no answer available for the item in this session. */
                if ($this->mandatory=='Y' && is_array($notanswered))
                {
                    //Go through each labelcode and check for a missing answer! If any are found, highlight this line
                    $emptyresult=0;
                    foreach($labelcode as $ld)
                    {
                        $myfname2=$myfname.'_'.$ld;
                        if((array_search($myfname2, $notanswered) !== FALSE) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == '')
                        {
                            $emptyresult=1;
                        }
                    }
                    if ($emptyresult == 1)
                    {
                        $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
                    }
                }

                // Get array_filter stuff
                $trbc = alternation($trbc , 'row');
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc subquestion-list questions-list");

                $answer .= $htmltbody2;

                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
                $answer .= "\t\t\t<th class=\"answertext\">\n"
                . "\t\t\t\t".$hiddenfield
                . "$answertext\n"
                . "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {$answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];}
                $answer .= "\" />\n\t\t\t</th>\n";
                $thiskey=0;
                foreach ($labelcode as $ld)
                {

                    $myfname2=$myfname."_$ld";
                    $myfname2value = isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] : "";
                    $answer .= "\t<td class=\"answer_cell_00$ld answer-item text-item\">\n"
                    . "\t\t\t\t<label for=\"answer{$myfname2}\">\n"
                    . "\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" />\n"
                    . "\t\t\t\t<input type=\"text\" name=\"$myfname2\" id=\"answer{$myfname2}\" class=\"".$kpclass."\" {$maxlength} title=\""
                    . flattenText($labelans[$thiskey]).'" '
                    . 'size="'.$inputwidth.'" '
                    . ' value="'.str_replace ('"', "'", str_replace('\\', '', $myfname2value))."\" />\n";
                    $answer .= "\t\t\t\t</label>\n\t\t\t</td>\n";
                    $thiskey += 1;
                }
                if (strpos($answertextsave,'|'))
                {
                    $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                    $answer .= "\t\t\t<td class=\"answertextright\" style=\"text-align:left;\" width=\"$answerwidth%\">$answertext</td>\n";
                }
                elseif ($right_exists)
                {
                    $answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width='$answerwidth%'>&nbsp;</td>\n";
                }

                $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $row_total);
                $answer .= "\n\t\t</tr>\n";
                //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
                $fn++;
            }
            if($show_totals == 'col' || $show_totals == 'both' || $grand_total == true)
            {
                $answer .= "\t\t<tr class=\"total\">$row_head";
                for( $a = 0; $a < count($labelcode) ; ++$a )
                {
                    $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $col_total);
                };
                $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $grand_total)."\n\t\t</tr>\n";
            }
            $answer .= "\t</tbody>\n</table>\n";
            if(!empty($q_table_id))
            {
                if ($aQuestionAttributes['numbers_only']==1)
                {
                    $radix = $sSeperator;
                }
                else {
                    $radix = 'X';   // to indicate that should not try to change entered values
                }
                $answer .= "\n<script type=\"text/javascript\">new multi_set('$q_table_id','$radix');</script>\n";
            }
            else
            {
                $addcheckcond = <<< EOD
<script type="text/javascript">
<!--
$(document).ready(function()
{
    $('#question{$this->id} :input:visible:enabled').each(function(index){
        $(this).bind('change',function(e) {
            checkconditions($(this).attr('value'), $(this).attr('name'), $(this).attr('type'));
            return true;
        })
    })
})
// -->
</script>
EOD;
                $answer .= $addcheckcond;
            }
        }
        else
        {
            $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        }
        return $answer;
    }
    
    //public function getInputNames() - inherited
    
    public function availableAttributes()
    {
        return array("answer_width","array_filter","array_filter_exclude","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","maximum_chars","min_answers","numbers_only","show_totals","show_grand_total","page_break","random_order","parent_order","text_input_width","random_group");
    }
}
?>