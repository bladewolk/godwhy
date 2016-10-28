<?php

namespace app\controllers;

use app\models\EntryForm;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new EntryForm();
        $request = Yii::$app->request->post();
        if ($model->load($request)) {
            // данные в $model удачно проверены
            // делаем что-то полезное с $model ...
            $model->file = UploadedFile::getInstance($model, 'file');
            $model->file->saveAs('files/' . $model->file->baseName . "." . $model->file->extension);
//            return $this->render('entry-confirm',
//                [
//                    'data' => 'DONE'
//                ]);

        }
        // либо страница отображается первый раз, либо есть ошибка в данных
        //создания списка файло .CVS
        $files = array_diff(scandir('files/'), array('..', '.'));
        $searchFiles = array_map(function ($item) {
            if (pathinfo($item, PATHINFO_EXTENSION) === 'csv') {
                return $item;
            }
        }, $files);

        return $this->render('entry',
            [
                'model' => $model,
                'files' => array_filter($searchFiles)
            ]);
    }


    public function actionEntry()
    {
        $model = new EntryForm();
        $request = Yii::$app->request->post();
        if ($model->load($request)) {
            // данные в $model удачно проверены
            // делаем что-то полезное с $model ...
            $model->file = UploadedFile::getInstance($model, 'file');
            $model->file->saveAs('files/' . $model->file->baseName . "." . $model->file->extension);
            return $this->render('entry-confirm',
                [
                    'data' => 'DONE'
                ]);

        }
        // либо страница отображается первый раз, либо есть ошибка в данных
        //создания списка файло .CVS
        $files = array_diff(scandir('files/'), array('..', '.'));
        $searchFiles = array_map(function ($item) {
            if (pathinfo($item, PATHINFO_EXTENSION) === 'csv') {
                return $item;
            }
        }, $files);

        return $this->render('entry',
            [
                'model' => $model,
                'files' => array_filter($searchFiles)
            ]);
    }

    function csv_to_array($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }


    public function actionRequest()
    {
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            /*
             * БЛОК парсинга для отображения на графике Google Charts :c
             * http://cdn.pophangover.com/wp-content/uploads/2012/07/disgusted-oh-god-why-text.png
             * парсинг из csv -> http://php.net/manual/ru/function.str-getcsv.php
             */
            $data = $this->csv_to_array('files/' . $data['filename']);
            $newArr = [];
            $i = 0;
            //переносим данные в новым массив в котором будут поля: file_upload & file_download
            foreach ($data as $key => $value) {
                $newArr[$i]['issue_date'] = date_parse($value['issue_date'])['day'];
                if ($value['event_type'] == 'file_upload') {
                    $newArr[$i]['file_upload'] = 1;
                    $newArr[$i]['file_download'] = 0;
                } else {
                    $newArr[$i]['file_upload'] = 0;
                    $newArr[$i]['file_download'] = 1;
                }
                $i++;
            }
            //совмещаем массивы которые имею одинаковые дни
            $res = [];
            foreach ($newArr as $entry) {
                $key = $entry['issue_date'];
                if (!isset($res[$key])) {
                    $res[$key] = $entry;
                } else {
                    foreach ($entry as $field => $value) {
                        if ($field !== 'issue_date') {
                            if (!isset($res[$key][$field])) {
                                $res[$key][$field] = $value;
                            } else {
                                $res[$key][$field] += $value;
                            }
                        }
                    }
                }
            }
            //reset ключей основного массива
            $res = array_values($res);
            //очень некрасивое заполнение первого массива. нужно для google charts
            $result = [
                ['date', 'upload', 'download']
            ];
            //перенос данных в новый массив с ключами [0,1,2] вместо плохих ['issue_date'] etc.
            for ($i = 0; $i < count($res); $i++) {
                $result[$i + 1][0] = $res[$i]['issue_date'];
                $result[$i + 1][1] = $res[$i]['file_upload'];
                $result[$i + 1][2] = $res[$i]['file_download'];
            }
//Конец парсинга Google Charts
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'content' => $result,
            ];
        }
    }


    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public
    function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public
    function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public
    function actionAbout()
    {
        return $this->render('about');
    }
}
