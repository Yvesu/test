import React, { Component } from 'react'
import { Button, message } from 'antd'

class LoginHome extends Component{
  constructor(props){
    super(props)
    this.state={
      bgVideo:''
    }
  }
  handMessage = ()=>{
    message.info('暂未开放,敬请期待!')
  }
  render(){
    // http://v.cdn.hivideo.com/web_bg.mp4
    // {this.state.bgVideo}
    return(
      <div className='home_content_pageOne'>
        <video src='http://v.cdn.hivideo.com/web_bg.mp4' autoPlay loop></video>
        <div className='content_mask'></div>
        <div className="content_top_free">
          <p>
            {/* <img src="./img/imoviestudio.png" alt="我的电影工作室"/> */}
            <img src="http://img.cdn.hivideo.com/web/img/imoviestudio.png" alt="我的电影工作室"/>
            {/* imoviestudio */}
          </p>
          <Button type='primary' onClick={this.handMessage} size='large'>免费注册</Button>
        </div>
        {/* <div className="hyper_link"> */}
          {/* <a href="#" target="_blank"> */}
            {/* <img src="./img/ffm_logo.png" alt="蒙特利尔国际电影节"/> */}
            {/* <img src="http://img.cdn.hivideo.com/web/img/ffm_logo.png" alt="蒙特利尔国际电影节"/> */}
          {/* </a> */}
          {/* <a href="#" target="_blank"> */}
            {/* <img src="./img/bcsff_logo.png" alt="北京大学生电影节"/> */}
            {/* <img src="http://img.cdn.hivideo.com/web/img/bcsff_logo.png" alt="北京大学生电影节"/> */}
          {/* </a> */}
        {/* </div> */}
      </div>
    )
  }
  // componentDidMount(){
  //   fetch('http://www.goobird.com/')
  //     .then((response)=>response.json())
  //     .then((res)=>{
  //       console.log(res);
  //       this.setState({
  //         bgVideo:res.data
  //       })
  //     })
  // }
}

export default LoginHome
