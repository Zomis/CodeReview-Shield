# CodeReview-Shield [![Code Review](http://www.zomis.net/codereview/shield/?qid=95459&dummy)](http://codereview.stackexchange.com/q/95459/31562)

get a shields.io-like 'shield' for your Code Review question

# Usage

Add this to your GitHub repository `README.md` file, or anywhere else that supports markdown:

    [![Code Review](http://www.zomis.net/codereview/shield/?qid=qqqqq)](http://codereview.stackexchange.com/q/qqqqq/uuuuuuu)

Replace `qqqqq` with the question id and `uuuuuuu` with your user id.

# Modes

By default, the shield decides the mode depending on the status of the question.

- If the question does not have any answers, the mode `score` is used with red background
- If the question has answers but no accepted answers, the mode `answers` is used with orange background
- If the question has an accepted answer, the mode `views` is used with green background

You can specify the mode yourself in the URL by using `&mode=yourmode`, for example:

### score mode

    [![Code Review](http://www.zomis.net/codereview/shield/?qid=95459&mode=score)](http://codereview.stackexchange.com/q/95459/31562)
    
[![Code Review](http://www.zomis.net/codereview/shield/?qid=95459&mode=score)](http://codereview.stackexchange.com/q/95459/31562)
    
### answers mode

    [![Code Review](http://www.zomis.net/codereview/shield/?qid=95459&mode=answers)](http://codereview.stackexchange.com/q/95459/31562)

[![Code Review](http://www.zomis.net/codereview/shield/?qid=95459&mode=answers)](http://codereview.stackexchange.com/q/95459/31562)

### views mode

    [![Code Review](http://www.zomis.net/codereview/shield/?qid=95459&mode=views)](http://codereview.stackexchange.com/q/95459/31562)

[![Code Review](http://www.zomis.net/codereview/shield/?qid=95459&mode=views)](http://codereview.stackexchange.com/q/95459/31562)
