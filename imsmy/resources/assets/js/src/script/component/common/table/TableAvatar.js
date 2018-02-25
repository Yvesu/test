import React, { Component } from 'react'
import { Link } from 'react-router'
import { Avatar } from 'antd'
// import { Icon } from 'antd'

class TableAvatar extends Component{
  render(){
    return(
      <Link to={this.props.linkUri? this.props.linkUri : null}>
        <div className='user_avatar_table_box'>
          <Avatar src={this.props.imgUri} size="large"/>
        </div>
      </Link>
    )
  }
}

export default TableAvatar

  // <div className={this.props.noMask? 'cover_box_noneed_mask' : 'cover_box'}>
  //   <img className="detail_img_hover"
  //     src={this.props.imgUri}/>
  //     <div className="detail_img_mask">
  //       <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
  //     </div>
  // </div>
