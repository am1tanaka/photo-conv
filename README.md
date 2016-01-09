# photo-conv
写真の上限サイズを調整したり、ファイル名や指定の文字列からEXIFを書き込むPHPページ

## Version2
PHPを利用していたが、クライアントサイドで完結させたいので、JavaScript化を行う。

- 画像の拡大縮小
  - Processingjs(http://processingjs.org/)を利用する
- Exifの編集
  - [piexifjs](https://github.com/hMatoba/piexifjs)を利用する
