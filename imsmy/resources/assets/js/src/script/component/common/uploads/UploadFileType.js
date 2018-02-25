import { message } from 'antd'

export default {
  beforeUploadImg:(file)=>{
    const isJPG = file.type === 'image/jpeg';
    const isPNG = file.type === 'image/png';
    const isGIF = file.type === 'image/gif';
    if (!isJPG && !isPNG && !isGIF) {
      message.error('格式不支持');
      return false
    }
    const isLt5M = file.size / 1024 / 1024 < 5;
    // console.log(file.size);
    if (!isLt5M) {
      message.error('图片大小不能超过5M!');
    }
    // return false
    return isJPG && isLt5M || isPNG && isLt5M || isGIF && isLt5M;
  },
  beforeUploadAudio:(file)=>{
    const isMP3 = file.type === 'audio/mp3';
    if (!isMP3) {
      message.error('格式不支持');
      return false
    }
    const isLt50M = file.size / 1024 / 1024 < 50;
    if (!isLt50M) {
      message.error('音频大小不能超过50M!');
    }
    return isMP3 && isLt50M;
  },
  beforeUploadVideo:(file)=>{
    const isMP4 = file.type == 'video/mp4';
    const isMOV = file.type === 'video/mov';
    const isQUICKTIME = file.type === 'video/quicktime'
    if (!isMP4 && !isMOV && !isQUICKTIME) {
      message.error('格式不支持');
      return false
    }
    const isLt50M = file.size / 1024 / 1024 < 50;
    if (!isLt50M) {
      message.error('音频大小不能超过50M!');
    }
    return isMP4 && isLt50M  || isMOV && isLt50M || isQUICKTIME && isLt50M;
  },
  beforeUploadFx:(file)=>{
    // console.log(file.type);
    const isMOV = file.type === 'video/mov';
    if (!isMOV) {
      message.error('格式不支持');
      return false
    }
    return isMOV ;
  },
  beforeUploadZIP:(file)=>{
    // console.log(file);
    // console.log(file.type);
    const isZIP = file.type === 'application/zip'
    if(!isZIP){
      message.error('格式不支持');
      return false
    }
    return isZIP ;
  },
  beforeUploadHiColor:(file)=>{
    const suffix=file.name.replace(/^.+\./,'')
    const isLt5M = file.size / 1024 / 1024 < 5;
    if(!isLt5M){
      message.error('文件大小不能超过5M!');
      return false
    }
    if(suffix != 'hicolor'){
      message.error('格式不支持');
      return false
    }
    return suffix && isLt5M
  },
  beforeUploadVein:(file)=>{
    const isJPG = file.type === 'image/jpeg';
    const isPNG = file.type === 'image/png';
    if (!isJPG && !isPNG ) {
      message.error('格式不支持');
      return false
    }
    const isLt5M = file.size / 1024 / 1024 < 5;
    // console.log(file.size);
    if (!isLt5M) {
      message.error('文件大小不能超过5M!');
    }
    // return false
    return isJPG && isLt5M || isPNG && isLt5M;
  },
  beforeUploadLibraryCover:(file)=>{
    const isJPG = file.type === 'image/jpeg';
    const isGIF = file.type === 'image/gif';
    if (!isJPG && !isGIF) {
      message.error('格式不支持');
      return false
    }
    const isLt5M = file.size / 1024 / 1024 < 5;
    // console.log(file.size);
    if (!isLt5M) {
      message.error('图片大小不能超过5M!');
    }
    // return false
    return isJPG && isLt5M || isGIF && isLt5M;
  },
  beforeUploadLibraryVideo:(file)=>{
    const isMP4 = file.type == 'video/mp4';
    const isQUICKTIME = file.type === 'video/quicktime'
    if (!isMP4 && !isQUICKTIME) {
      message.error('格式不支持');
      return false
    }
    const isLt50M = file.size / 1024 / 1024 < 50;
    if (!isLt50M) {
      message.error('音频大小不能超过50M!');
    }
    return isMP4 && isLt50M  || isQUICKTIME && isLt50M;
  },
}
